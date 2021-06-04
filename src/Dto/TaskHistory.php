<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class TaskHistory extends DataTransferObject
{
    public string $id;

    public string $processDefinitionKey;

    public string $processDefinitionId;

    public string $processInstanceId;

    public string $executionId;

    public ?string $caseDefinitionKey;

    public ?string $caseDefinitionId;

    public ?string $caseInstanceId;

    public ?string $caseExecutionId;

    public string $activityInstanceId;

    public string $name;

    public ?string $description;

    public ?string $deleteReason;

    public ?string $owner;

    public ?string $assignee;

    #[CastWith(CarbonCaster::class)]
    public Carbon $startTime;

    #[CastWith(CarbonCaster::class)]
    public ?Carbon $endTime;

    public ?int $duration;

    public string $taskDefinitionKey;

    public int $priority;

    public ?Carbon $due;

    public ?string $parentTaskId;

    public ?Carbon $followUp;

    public ?string $tenantId;

    #[CastWith(CarbonCaster::class)]
    public ?Carbon $removalTime;

    public string $rootProcessInstanceId;
}
