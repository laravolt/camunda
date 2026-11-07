<?php

namespace Laravolt\Camunda\Tests\Http;

use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Laravolt\Camunda\Exceptions\InvalidArgumentException;
use Laravolt\Camunda\Http\ExternalTaskClient;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\TaskClient;
use Laravolt\Camunda\Tests\TestCase;

/**
 * Offline checks that caller values stay inside one path segment or query value,
 * and that only supported variable types and formats are forwarded.
 */
class RequestSafetyTest extends TestCase
{
    private const BASE = 'http://camunda.test/engine-rest';

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('services.camunda.url', self::BASE);
    }

    public function test_identifiers_are_encoded_as_one_path_segment(): void
    {
        Http::fake(['*' => Http::response(['bpmn20Xml' => '<xml/>'])]);

        ProcessDefinitionClient::xml('?key=a&x');
        ProcessDefinitionClient::xml(key: 'a/b', tenantId: 't#1');

        $urls = Http::recorded()->map(fn ($pair) => $pair[0]->url())->all();
        $this->assertSame([
            self::BASE.'/process-definition/%3Fkey%3Da%26x/xml',
            self::BASE.'/process-definition/key/a%2Fb/tenant-id/t%231/xml',
        ], $urls);
    }

    public function test_process_instance_id_is_sent_as_a_query_value(): void
    {
        Http::fake(['*' => Http::response([])]);

        TaskClient::getByProcessInstanceId('pi-1&assignee=x');
        ExternalTaskClient::getByProcessInstanceId('pi-1&locked=true');

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE.'/task?processInstanceId=pi-1%26assignee%3Dx');
        Http::assertSent(fn (Request $r) => $r->url() === self::BASE.'/external-task?processInstanceId=pi-1%26locked%3Dtrue');
    }

    public function test_unclaim_posts_to_the_unclaim_endpoint(): void
    {
        Http::fake(['*' => Http::response(null, 204)]);

        $this->assertTrue(TaskClient::unclaim('task-1'));
        Http::assertSent(fn (Request $r) => $r->url() === self::BASE.'/task/task-1/unclaim');
    }

    public function test_java_serialized_object_variables_are_rejected(): void
    {
        Http::fake();

        try {
            TaskClient::submit('task-1', [
                'payload' => [
                    'value' => 'rO0ABQ==',
                    'type' => 'Object',
                    'valueInfo' => [
                        'objectTypeName' => 'java.util.HashMap',
                        'serializationDataFormat' => 'application/x-java-serialized-object',
                    ],
                ],
            ]);
            $this->fail('Expected the variable to be rejected');
        } catch (InvalidArgumentException) {
            Http::assertNothingSent();
        }
    }

    public function test_unknown_variable_types_are_rejected(): void
    {
        Http::fake();

        $this->expectException(InvalidArgumentException::class);
        ProcessDefinitionClient::start(key: 'process_1', variables: ['v' => ['value' => 1, 'type' => 'SomethingElse']]);
    }

    public function test_supported_variable_types_are_forwarded(): void
    {
        Http::fake(['*' => Http::response(null, 204)]);

        $this->assertTrue(TaskClient::submit('task-1', [
            'title' => ['value' => 'Hello', 'type' => 'String'],
            'items' => ['value' => '[1,2]', 'type' => 'Json'],
            'tags' => ['value' => '["a"]', 'type' => 'Object', 'valueInfo' => [
                'objectTypeName' => 'java.util.ArrayList',
                'serializationDataFormat' => 'application/json',
            ]],
        ]));
        Http::assertSentCount(1);
    }
}
