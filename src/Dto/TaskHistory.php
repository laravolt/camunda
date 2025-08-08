<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Spatie\LaravelData\Attributes\WithCast;
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
        #[WithCast(CarbonCaster::class)]
        public Carbon $startTime,
        #[WithCast(CarbonCaster::class)]
        public ?Carbon $endTime,
        public ?int $duration,
        public string $taskDefinitionKey,
        public int $priority,
        #[WithCast(CarbonCaster::class)]
        public ?Carbon $due,
        public ?string $parentTaskId,
        #[WithCast(CarbonCaster::class)]
        public ?Carbon $followUp,
        public ?string $tenantId,
        #[WithCast(CarbonCaster::class)]
        public ?Carbon $removalTime,
        public string $rootProcessInstanceId,
        public ?string $taskState,
    ) {}
}
