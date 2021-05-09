<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Laravolt\Camunda\Dto\Casters\VariablesCaster;
use Spatie\DataTransferObject\Attributes\CastWith;
use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class ProcessInstance extends DataTransferObject
{
    public string $id;

    public string|null $tenantId;

    public string|null $businessKey;

    public array $links;

    public string $definitionId;

    public ?string $caseInstanceId;

    public bool $ended;

    public bool $suspended;

    /** @var \Laravolt\Camunda\Dto\Variable[]  */
    #[CastWith(VariablesCaster::class, Variable::class)]
    public array|null $variables;
}
