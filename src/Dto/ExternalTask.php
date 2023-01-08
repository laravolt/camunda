<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Laravolt\Camunda\Dto\Casters\VariablesCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class ExternalTask extends DataTransferObject
{
    public string $id;

    public string $topicName;

    public string|null $workerId;

    public string|null $activityId;

    public string|null $activityInstanceId;

    public string|null $errorMessage;

    public string|null $errorDetails;

    public string|null $executionId;

    public string|null $businessKey;

    #[CastWith(CarbonCaster::class)]
    public Carbon|null $lockExpirationTime;

    public string $processDefinitionId;

    public string $processDefinitionKey;

    public string|null $processDefinitionVersionTag;

    public string $processInstanceId;

    public string|null $tenantId;

    public ?int $retries;

    public int $priority;

    /** @var \Laravolt\Camunda\Dto\Variable[] */
    #[CastWith(VariablesCaster::class, Variable::class)]
    public array|null $variables;

    public bool $suspended;

    public array|null $extensionProperties;
}
