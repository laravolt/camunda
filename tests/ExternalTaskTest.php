<?php

namespace Laravolt\Camunda\Tests;

use Laravolt\Camunda\Dto\TaskHistory;
use Laravolt\Camunda\Dto\Variable;
use Laravolt\Camunda\Exceptions\CamundaException;
use Laravolt\Camunda\Exceptions\ObjectNotFoundException;
use Laravolt\Camunda\Http\DeploymentClient;
use Laravolt\Camunda\Http\ExternalTaskClient;
use Laravolt\Camunda\Http\ProcessDefinitionClient;
use Laravolt\Camunda\Http\TaskClient;
use Laravolt\Camunda\Http\TaskHistoryClient;

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
        $variables = ['signature' => ['value' => 'Fulan', 'type' => 'string']];
        $businessKey = 'key-1';
        ProcessDefinitionClient::start(key: 'processExternalTask', variables: $variables, businessKey: $businessKey);
        $topics = [
            ['topicName' => 'pdf', 'lockDuration' => 600_000]
        ];
        $externalTasks = ExternalTaskClient::fetchAndLock('worker1', $topics);
        $this->assertCount(1, $externalTasks);
    }

    protected function tearDown(): void
    {
        $this->truncateDeployment();
    }
}
