<?php

namespace Laravolt\Camunda\Dto;

use Carbon\Carbon;
use Laravolt\Camunda\Dto\Casters\CarbonCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class Deployment extends DataTransferObject
{
    public string $id;

    public string|null $tenantId;

    public ?string $name;

    public ?string $source;

    #[CastWith(CarbonCaster::class)]
    public Carbon $deploymentTime;

    public ?array $processDefinitions = [];

    public array|null $links;

    public array|null $deployedProcessDefinitions;

    public array|null $deployedCaseDefinitions;

    public array|null $deployedDecisionDefinitions;

    public array|null $deployedDecisionRequirementsDefinitions;
}
