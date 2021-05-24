<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class ProcessInstanceHistory extends DataTransferObject
{
    public string $id;

    public ?string $rootProcessInstanceId;

    public ?string $superProcessInstanceId;

    public ?string $superCaseInstanceId;

    public ?string $caseInstanceId;

    public ?string $processDefinitionName;

    public string $processDefinitionKey;

    public string $processDefinitionVersion;

    public string $processDefinitionId;

    public ?string $businessKey;

    #[CastWith(CarbonCaster::class)]
    public Carbon $startTime;

    #[CastWith(CarbonCaster::class)]
    public ?Carbon $endTime;

    #[CastWith(CarbonCaster::class)]
    public ?Carbon $removalTime;

    public ?int $durationInMillis;

    public ?string $startUserId;

    public string $startActivityId;

    public ?string $deleteReason;

    public ?string $tenantId;

    public string $state;
}
