<?php

namespace Laravolt\Camunda\Dto;

use Illuminate\Support\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class Deployment extends Data
{
    public function __construct(
        public string $id,
        public ?string $tenantId,
        public ?string $name,
        public ?string $source,
        #[WithCast(CarbonCaster::class)]
        public Carbon $deploymentTime,
        public ?array $processDefinitions = [],
        public ?array $links = [],
        public ?array $deployedProcessDefinitions = [],
        public ?array $deployedCaseDefinitions = [],
        public ?array $deployedDecisionDefinitions = [],
        public ?array $deployedDecisionRequirementsDefinitions = [],
    ) {}
}
