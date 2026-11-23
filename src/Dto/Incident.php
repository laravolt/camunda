<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

/**
 * An open incident, for example an external task or job that ran out of retries.
 */
class Incident extends Data
{
    public function __construct(
        public string $id,
        public string $incidentType,
        public ?string $incidentMessage = null,
        public ?string $processDefinitionId = null,
        public ?string $processInstanceId = null,
        public ?string $executionId = null,
        public ?string $activityId = null,
        public ?string $failedActivityId = null,
        public ?string $causeIncidentId = null,
        public ?string $rootCauseIncidentId = null,
        /** Id of the failed job or external task, depending on the incident type. */
        public ?string $configuration = null,
        public ?string $tenantId = null,
        public ?string $jobDefinitionId = null,
        public ?string $annotation = null,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public ?Carbon $incidentTimestamp = null,
    ) {}
}
