<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Http\DeploymentClient;
use Laravolt\Camunda\Http\ExternalTaskClient;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\ProcessInstanceHistoryClient;

class ExternalTaskTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $files = [__DIR__.'/../resources/external-task.bpmn'];

        DeploymentClient::create('External Task', $files);
    }

    public function test_fetch_and_lock()
    {
        $this->createExternalProcess();
        $topics = [
            ['topicName' => 'pdf', 'lockDuration' => 600_000]
        ];
        $externalTasks = ExternalTaskClient::fetchAndLock('worker1', $topics);
        $this->assertCount(1, $externalTasks);
    }

    public function test_complete_task()
    {
        $this->createExternalProcess();
        $topics = [
            ['topicName' => 'pdf', 'lockDuration' => 600_000]
        ];
        $externalTasks = ExternalTaskClient::fetchAndLock('worker1', $topics);
        $this->assertCount(1, $externalTasks);
        $task = $externalTasks[0];
        $completed = ExternalTaskClient::complete($task->id, 'worker1');
        $this->assertTrue($completed);
    }

    public function test_complete_task_with_variables()
    {
        $processInstance = $this->createExternalProcess();
        $topics = [
            ['topicName' => 'pdf', 'lockDuration' => 600_000]
        ];
        $externalTasks = ExternalTaskClient::fetchAndLock('worker1', $topics);
        $this->assertCount(1, $externalTasks);
        $task = $externalTasks[0];
        $completed = ExternalTaskClient::complete(
            $task->id,
            'worker1',
            ['title' => ['value' => 'Sample Title', 'type' => 'string']]
        );
        $this->assertTrue($completed);
        $processInstanceHistory = ProcessInstanceHistoryClient::find($processInstance->id);
        $this->assertEquals('COMPLETED', $processInstanceHistory->state);
        $variables = ProcessInstanceHistoryClient::variables($processInstance->id);
        $this->assertArrayHasKey('signature', $variables);
        $this->assertArrayHasKey('title', $variables);
    }

    public function test_unlock_locked_task()
    {
        $this->createExternalProcess();
        $topics = [
            ['topicName' => 'pdf', 'lockDuration' => 600_000]
        ];
        $externalTasks = ExternalTaskClient::fetchAndLock('worker1', $topics);
        $task = $externalTasks[0];
        $unlocked = ExternalTaskClient::unlock($task->id);
        $this->assertTrue($unlocked);
    }

    public function test_unlock_unlocked_task()
    {
        $processInstance = $this->createExternalProcess();
        $tasks = ExternalTaskClient::getByProcessInstanceId($processInstance->id);
        $task = $tasks[0];
        $unlocked = ExternalTaskClient::unlock($task->id);
        $this->assertTrue($unlocked);
    }

    protected function tearDown(): void
    {
        $this->truncateDeployment();
    }

    private function createExternalProcess(): \Laravolt\Camunda\Dto\ProcessInstance
    {
        $variables = ['signature' => ['value' => 'Fulan', 'type' => 'string']];
        $businessKey = 'key-1';

        return ProcessDefinitionClient::start(key: 'processExternalTask', variables: $variables, businessKey: $businessKey);
    }
}
