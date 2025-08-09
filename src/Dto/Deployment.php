<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Data;

class Deployment extends Data
{
    public function __construct(
        public string $id,
        public ?string $name,
        public ?string $source,
        #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d\TH:i:s.vO')]
        public Carbon $deploymentTime,
        public ?array $processDefinitions = [],
        public ?array $links = [],
        public ?array $deployedProcessDefinitions = [],
        public ?array $deployedCaseDefinitions = [],
        public ?array $deployedDecisionDefinitions = [],
        public ?array $deployedDecisionRequirementsDefinitions = [],
        public ?string $tenantId = null,
    ) {}
}
