<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Data;

class TaskHistory extends Data
{
    public function __construct(
        public string $id,
        public string $processDefinitionKey,
        public string $processDefinitionId,
        public string $processInstanceId,
        public string $executionId,
        public ?string $caseDefinitionKey,
        public ?string $caseDefinitionId,
        public ?string $caseInstanceId,
        public ?string $caseExecutionId,
        public string $activityInstanceId,
        public string $name,
        public ?string $description,
        public ?string $deleteReason,
        public ?string $owner,
        public ?string $assignee,
        public Carbon $startTime,
        public ?Carbon $endTime,
        public ?int $duration,
        public string $taskDefinitionKey,
        public int $priority,
        public ?Carbon $due,
        public ?string $parentTaskId,
        public ?Carbon $followUp,
        public ?string $tenantId,
        public ?Carbon $removalTime,
        public string $rootProcessInstanceId,
    ) {}
}
