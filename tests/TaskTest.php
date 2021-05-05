<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Exceptions\CamundaException;
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

    public function test_find_by_id()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $tasks = ProcessInstanceClient::tasks($processInstance->id);

        foreach ($tasks as $task) {
            $tastObject = TaskClient::find($task->id);
            $this->assertEquals($task->id, $tastObject->id);
        }
    }

    public function test_handle_invalid_id()
    {
        $this->expectException(ObjectNotFoundException::class);
        TaskClient::find('invalid-id');
    }

    public function test_submit_form()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $tasks = ProcessInstanceClient::tasks($processInstance->id);

        foreach ($tasks as $task) {
            $submitted = TaskClient::submit($task->id, ['email' => ['value' => 'uyab.exe@gmail.com', 'type' => 'string']]);
            $this->assertTrue($submitted);
        }
    }

    public function test_submit_form_with_invalid_id()
    {
        $this->expectException(CamundaException::class);
        TaskClient::submit('invalid-id', ['foo' => 'bar']);
    }

    protected function tearDown(): void
    {
        $this->undeploySampleBpmn();
    }
}
