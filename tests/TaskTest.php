<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;
use Laravolt\Camunda\Http\TaskClient;

class TaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->deploySampleBpmn();
    }

    public function test_it_can_find_by_id()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $tasks = ProcessInstanceClient::tasks($processInstance->id);

        foreach ($tasks as $task) {
            $tastObject = TaskClient::find($task->id);
            $this->assertEquals($task->id, $tastObject->id);
        }
    }

    public function test_it_can_handle_invalid_id()
    {
        $this->expectException(ObjectNotFoundException::class);
        TaskClient::find('invalid-id');
    }

    protected function tearDown(): void
    {
        $this->undeploySampleBpmn();
    }
}
