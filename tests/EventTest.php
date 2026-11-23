<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\DeploymentClient;
use Laravolt\Camunda\Http\MessageEventClient;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;
use Laravolt\Camunda\Http\SignalEventClient;
use Laravolt\Camunda\Http\TaskClient;
use PHPUnit\Framework\Attributes\Group;

#[Group('integration')]
class EventTest extends IntegrationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        DeploymentClient::create('events', [__DIR__.'/../resources/events.bpmn']);
    }

    public function test_message_start_returns_the_started_instance(): void
    {
        $instance = MessageEventClient::start(
            messageName: 'order_placed',
            businessKey: 'order-42',
            variables: ['amount' => ['value' => 150, 'type' => 'integer']],
        );

        $this->assertSame('order-42', $instance->businessKey);
        $this->assertSame('review_order', TaskClient::getByProcessInstanceId($instance->id)[0]->taskDefinitionKey);
        $this->assertSame(150, ProcessInstanceClient::variables($instance->id)['amount']->value);
    }

    public function test_correlate_reaches_the_waiting_instance(): void
    {
        $instance = ProcessDefinitionClient::start(key: 'catch_process', businessKey: 'invoice-7');

        $results = MessageEventClient::correlate(
            messageName: 'payment_received',
            businessKey: 'invoice-7',
            processVariables: ['paid' => ['value' => true, 'type' => 'boolean']],
        );

        $this->assertCount(1, $results);
        $this->assertSame('Execution', $results[0]->resultType);
        $this->assertTrue(ProcessInstanceClient::variables($instance->id)['paid']->value);
    }

    public function test_correlate_without_a_match_throws_not_found(): void
    {
        $this->expectException(ObjectNotFoundException::class);

        MessageEventClient::correlate(messageName: 'payment_received', businessKey: 'nobody-waits');
    }

    public function test_signal_moves_waiting_instances(): void
    {
        $instance = ProcessDefinitionClient::start(key: 'catch_process', businessKey: 'invoice-8');
        MessageEventClient::correlate(messageName: 'payment_received', processInstanceId: $instance->id);

        $this->assertSame([], TaskClient::getByProcessInstanceId($instance->id));
        $this->assertTrue(SignalEventClient::throw('release_all'));
        $this->assertSame('after_release', TaskClient::getByProcessInstanceId($instance->id)[0]->taskDefinitionKey);
    }

    public function test_suspend_activate_and_set_variables(): void
    {
        $instance = ProcessDefinitionClient::start(key: 'catch_process', businessKey: 'invoice-9');

        $this->assertTrue(ProcessInstanceClient::suspend($instance->id));
        $this->assertTrue(ProcessInstanceClient::find($instance->id)->suspended);
        $this->assertTrue(ProcessInstanceClient::activate($instance->id));
        $this->assertFalse(ProcessInstanceClient::find($instance->id)->suspended);

        ProcessInstanceClient::setVariables($instance->id, ['note' => ['value' => 'checked', 'type' => 'string']]);
        $this->assertSame('checked', ProcessInstanceClient::variables($instance->id)['note']->value);

        ProcessInstanceClient::setVariables($instance->id, deletions: ['note']);
        $this->assertArrayNotHasKey('note', ProcessInstanceClient::variables($instance->id));
    }

    protected function tearDown(): void
    {
        try {
            $this->truncateDeployment();
        } finally {
            parent::tearDown();
        }
    }
}
