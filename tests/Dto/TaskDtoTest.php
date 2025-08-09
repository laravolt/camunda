<?php

namespace Laravolt\Camunda\Tests\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Task;
use Laravolt\Camunda\Tests\TestCase;

class TaskDtoTest extends TestCase
{
    public function test_created_and_last_updated_are_carbon_instances(): void
    {
        $payload = [
            'id' => 'task-123',
            'name' => 'Sample Task',
            'assignee' => null,
            'created' => '2025-08-09T06:50:00+00:00',
            'due' => null,
            'followUp' => null,
            'lastUpdated' => '2025-08-09T07:10:11+00:00',
            'delegationState' => null,
            'description' => null,
            'executionId' => 'exec-1',
            'owner' => null,
            'parentTaskId' => null,
            'priority' => '50',
            'processDefinitionId' => 'proc-def-1',
            'processInstanceId' => 'proc-inst-1',
            'taskDefinitionKey' => 'task_def_key',
            'caseExecutionId' => null,
            'caseInstanceId' => null,
            'caseDefinitionId' => null,
            'suspended' => false,
            'formKey' => null,
            'camundaFormRef' => null,
            'tenantId' => null,
            'taskState' => null,
        ];

        $task = Task::from($payload);

        $this->assertInstanceOf(Carbon::class, $task->created);
        $this->assertInstanceOf(Carbon::class, $task->lastUpdated);
    }

    public function test_last_updated_can_be_null(): void
    {
        $payload = [
            'id' => 'task-456',
            'name' => 'Another Task',
            'assignee' => null,
            'created' => '2025-08-09T06:50:00+00:00',
            'due' => null,
            'followUp' => null,
            'lastUpdated' => null,
            'delegationState' => null,
            'description' => null,
            'executionId' => 'exec-2',
            'owner' => null,
            'parentTaskId' => null,
            'priority' => '10',
            'processDefinitionId' => 'proc-def-2',
            'processInstanceId' => 'proc-inst-2',
            'taskDefinitionKey' => 'task_def_key_2',
            'caseExecutionId' => null,
            'caseInstanceId' => null,
            'caseDefinitionId' => null,
            'suspended' => true,
            'formKey' => null,
            'camundaFormRef' => null,
            'tenantId' => null,
            'taskState' => null,
        ];

        $task = Task::from($payload);

        $this->assertInstanceOf(Carbon::class, $task->created);
        $this->assertNull($task->lastUpdated);
    }
}
