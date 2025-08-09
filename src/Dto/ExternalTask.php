<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class ExternalTask extends Data
{
    public function __construct(
        public ?string $activityId,
        public ?string $activityInstanceId,
        public ?string $errorMessage,
        public ?string $errorDetails,
        public ?string $executionId,
        public string $id,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public ?Carbon $lockExpirationTime,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public ?Carbon $createTime,
        public string $processDefinitionId,
        public string $processDefinitionKey,
        public ?string $processDefinitionVersionTag,
        public string $processInstanceId,
        public ?int $retries,
        public bool $suspended,
        public ?string $workerId,
        public string $topicName,
        public ?string $tenantId,
        public ?array $variables,
        public int $priority,
        public ?string $businessKey = null,
        public ?array $extensionProperties = [],
    ) {}
}
