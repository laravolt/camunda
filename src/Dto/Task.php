<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class Task extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $assignee,
        #[WithCast(CarbonCaster::class)]
        public Carbon $created,
        public ?string $due,
        public ?string $followUp,
        #[WithCast(CarbonCaster::class)]
        public ?Carbon $lastUpdated,
        public ?string $delegationState,
        public ?string $description,
        public string $executionId,
        public ?string $owner,
        public ?string $parentTaskId,
        public string $priority,
        public string $processDefinitionId,
        public string $processInstanceId,
        public string $taskDefinitionKey,
        public ?string $caseExecutionId,
        public ?string $caseInstanceId,
        public ?string $caseDefinitionId,
        public bool $suspended,
        public ?string $formKey = null,
        public ?array $camundaFormRef = null,
        public ?string $tenantId = null,
        public ?string $taskState = null,
    ) {}
}
