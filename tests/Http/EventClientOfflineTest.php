<?php

namespace Laravolt\Camunda\Tests\Http;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravolt\Camunda\Dto\Incident;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Exceptions\UnexpectedResponseException;
use Laravolt\Camunda\Http\ExternalTaskClient;
use Laravolt\Camunda\Http\IncidentClient;
use Laravolt\Camunda\Http\MessageEventClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;
use Laravolt\Camunda\Http\SignalEventClient;
use Laravolt\Camunda\Tests\TestCase;

/**
 * Offline tests for the message, signal, incident and process-instance state calls.
 * Response bodies follow the Camunda 7 / Operaton REST API.
 */
class EventClientOfflineTest extends TestCase
{
    private const BASE = 'http://camunda.test/engine-rest';

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('services.camunda.url', self::BASE);
    }

    public function test_correlate_sends_the_message_and_hydrates_results(): void
    {
        Http::fake([self::BASE.'/message' => Http::response([
            ['resultType' => 'Execution', 'execution' => ['id' => 'ex-1', 'processInstanceId' => 'pi-1'], 'processInstance' => null],
        ])]);

        $results = MessageEventClient::correlate(
            messageName: 'payment_received',
            businessKey: 'invoice-7',
            correlationKeys: ['customer' => ['value' => 'C-1', 'type' => 'String']],
            processVariables: ['paid' => ['value' => true, 'type' => 'Boolean']],
            all: true,
        );

        $this->assertCount(1, $results);
        $this->assertSame('Execution', $results[0]->resultType);
        $this->assertSame('ex-1', $results[0]->execution['id']);
        Http::assertSent(fn (Request $request) => $request->url() === self::BASE.'/message'
            && $request['messageName'] === 'payment_received'
            && $request['businessKey'] === 'invoice-7'
            && $request['all'] === true
            && $request['resultEnabled'] === true
            && $request['correlationKeys']['customer']['value'] === 'C-1'
            && $request['processVariables']['paid']['value'] === true
            && ! isset($request['processInstanceId']));
    }

    public function test_correlate_without_a_match_throws_not_found(): void
    {
        Http::fake([self::BASE.'/message' => Http::response([
            'type' => 'RestException',
            'message' => 'Cannot correlate message \'x\': No process definition or execution matches the parameters',
        ], 400)]);

        $this->expectException(ObjectNotFoundException::class);
        MessageEventClient::correlate(messageName: 'x');
    }

    public function test_correlate_rejects_unsupported_variable_types(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);
        try {
            MessageEventClient::correlate(messageName: 'x', processVariables: ['v' => ['value' => 'a', 'type' => 'Serializable']]);
        } finally {
            Http::assertNothingSent();
        }
    }

    public function test_start_returns_the_started_process_instance(): void
    {
        Http::fake([self::BASE.'/message' => Http::response([[
            'resultType' => 'ProcessDefinition',
            'processInstance' => [
                'links' => [], 'id' => 'pi-9', 'definitionId' => 'message_start_process:1:a',
                'businessKey' => 'order-42', 'caseInstanceId' => null, 'ended' => false, 'suspended' => false, 'tenantId' => null,
            ],
        ]])]);

        $instance = MessageEventClient::start(messageName: 'order_placed', businessKey: 'order-42');

        $this->assertSame('pi-9', $instance->id);
        $this->assertSame('order-42', $instance->businessKey);
        // The old client sent a random processInstanceId, which no message start event can match.
        Http::assertSent(fn (Request $request) => ! isset($request['processInstanceId']));
    }

    public function test_start_throws_when_the_message_went_to_a_waiting_instance(): void
    {
        Http::fake([self::BASE.'/message' => Http::response([
            ['resultType' => 'Execution', 'execution' => ['id' => 'ex-1'], 'processInstance' => null],
        ])]);

        $this->expectException(ObjectNotFoundException::class);
        MessageEventClient::start(messageName: 'order_placed', businessKey: 'order-42');
    }

    public function test_signal_throw(): void
    {
        Http::fake([self::BASE.'/signal' => Http::response(null, 204)]);

        $this->assertTrue(SignalEventClient::throw('release_all', ['by' => ['value' => 'ops', 'type' => 'String']], tenantId: 't1'));
        Http::assertSent(fn (Request $request) => $request['name'] === 'release_all'
            && $request['variables']['by']['value'] === 'ops'
            && $request['tenantId'] === 't1');
    }

    public function test_signal_throw_error_response(): void
    {
        Http::fake([self::BASE.'/signal' => Http::response(['type' => 'RestException', 'message' => 'boom'], 500)]);

        $this->expectException(UnexpectedResponseException::class);
        SignalEventClient::throw('release_all');
    }

    public function test_incidents(): void
    {
        $incident = [
            'id' => 'inc-1', 'incidentType' => 'failedExternalTask', 'incidentMessage' => 'printer offline',
            'processInstanceId' => 'pi-1', 'configuration' => 'et-1', 'incidentTimestamp' => '2026-11-23T10:15:00.000+0700',
        ];
        Http::fake([
            self::BASE.'/incident/count*' => Http::response(['count' => 1]),
            self::BASE.'/incident/inc-1' => Http::response($incident),
            self::BASE.'/incident/missing' => Http::response(['type' => 'RestException', 'message' => 'not found'], 404),
            self::BASE.'/incident*' => Http::response([$incident]),
        ]);

        $incidents = IncidentClient::get(['processInstanceId' => 'pi-1']);

        $this->assertContainsOnlyInstancesOf(Incident::class, $incidents);
        $this->assertSame('et-1', $incidents[0]->configuration);
        $this->assertSame('2026-11-23', $incidents[0]->incidentTimestamp->format('Y-m-d'));
        $this->assertSame('inc-1', IncidentClient::find('inc-1')->id);
        $this->assertSame(1, IncidentClient::count(['processInstanceId' => 'pi-1']));

        $this->expectException(ObjectNotFoundException::class);
        IncidentClient::find('missing');
    }

    public function test_external_task_retries_and_failure(): void
    {
        Http::fake([
            self::BASE.'/external-task/et-1/retries' => Http::response(null, 204),
            self::BASE.'/external-task/et-1/failure' => Http::response(null, 204),
        ]);

        $this->assertTrue(ExternalTaskClient::setRetries('et-1', 3));
        $this->assertTrue(ExternalTaskClient::fail('et-1', 'worker1', 'timeout', retries: 2));

        Http::assertSent(fn (Request $request) => $request->method() === 'PUT' && $request['retries'] === 3);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/failure') && $request['retries'] === 2);
    }

    public function test_external_task_negative_retries(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ExternalTaskClient::setRetries('et-1', -1);
    }

    public function test_suspend_activate_and_set_variables(): void
    {
        Http::fake([
            self::BASE.'/process-instance/pi-1/suspended' => Http::response(null, 204),
            self::BASE.'/process-instance/pi-1/variables' => Http::response(null, 204),
            self::BASE.'/process-instance/gone/suspended' => Http::response(['type' => 'RestException', 'message' => 'gone'], 404),
        ]);

        $this->assertTrue(ProcessInstanceClient::suspend('pi-1'));
        $this->assertTrue(ProcessInstanceClient::activate('pi-1'));
        $this->assertTrue(ProcessInstanceClient::setVariables('pi-1', ['note' => ['value' => 'ok', 'type' => 'String']], ['old']));

        Http::assertSent(fn (Request $request) => $request->method() === 'PUT' && $request['suspended'] === true);
        Http::assertSent(fn (Request $request) => $request->method() === 'PUT' && $request['suspended'] === false);
        Http::assertSent(fn (Request $request) => str_ends_with($request->url(), '/variables')
            && $request['modifications']['note']['value'] === 'ok'
            && $request['deletions'] === ['old']);

        $this->expectException(ObjectNotFoundException::class);
        ProcessInstanceClient::suspend('gone');
    }

    public function test_set_variables_needs_something_to_change(): void
    {
        $this->expectException(InvalidArgumentException::class);
        ProcessInstanceClient::setVariables('pi-1');
    }
}
