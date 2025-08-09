<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class Task extends Data
{
    public function __construct(
        public string $id,
        public string $name,
        public ?string $assignee,
        #[WithCast(DateTimeInterfaceCast::class, format: ['Y-m-d\TH:i:s.vO', 'Y-m-d\TH:i:sP'])]
        public Carbon $created,
        public ?string $due,
        public ?string $followUp,
        #[WithCast(DateTimeInterfaceCast::class, format: ['Y-m-d\TH:i:s.vO', 'Y-m-d\TH:i:sP'])]
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
