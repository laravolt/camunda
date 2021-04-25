<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceClient;

class ProcessInstanceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->deploySampleBpmn();
    }

    public function test_get_process_instance()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance1 = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $processInstance2 = ProcessInstanceClient::find(id: $processInstance1->id);
        $processInstance3 = ProcessInstanceClient::find($processInstance1->id);

        $this->assertEquals($processInstance1->id, $processInstance2->id);
        $this->assertEquals($processInstance2->id, $processInstance3->id);
    }

    public function test_get_tasks()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $tasks = ProcessInstanceClient::tasks($processInstance->id);
        $this->assertCount(1, $tasks);
    }

    public function test_get_completed_tasks()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $completedTasks = ProcessInstanceClient::completedTasks($processInstance->id);
        $this->assertCount(1, $completedTasks);
    }

    public function test_delete()
    {
        $variables = ['title' => ['value' => 'Foo', 'type' => 'string']];
        $processInstance = ProcessDefinitionClient::start(key: 'process_1', variables: $variables);
        $deleted = ProcessInstanceClient::delete($processInstance->id);
        $this->assertTrue($deleted);

        $this->expectException(ObjectNotFoundException::class);
        ProcessInstanceClient::find($processInstance->id);
    }

    protected function tearDown(): void
    {
        $this->undeploySampleBpmn();
    }
}
