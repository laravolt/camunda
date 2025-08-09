<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class ProcessInstanceHistory extends Data
{
    public function __construct(
        public string $id,
        public ?string $businessKey,
        public string $processDefinitionId,
        public string $processDefinitionKey,
        public ?string $processDefinitionName,
        public int $processDefinitionVersion,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public Carbon $startTime,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public ?Carbon $endTime,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public ?Carbon $removalTime,
        public ?int $durationInMillis,
        public ?string $startUserId,
        public string $startActivityId,
        public ?string $deleteReason,
        public ?string $rootProcessInstanceId,
        public ?string $superProcessInstanceId,
        public ?string $superCaseInstanceId,
        public ?string $caseInstanceId,
        public ?string $tenantId,
        public string $state,
        public ?string $restartedProcessInstanceId,
    ) {}
}
