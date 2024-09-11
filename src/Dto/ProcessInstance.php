<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Laravolt\Camunda\Dto\Casters\VariablesCaster;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Data;

class ProcessInstance extends Data
{
    public function __construct(
        public array $links,
        public string $id,
        public string $definitionId,
        public ?string $businessKey,
        public ?string $caseInstanceId,
        public bool $ended,
        public bool $suspended,
        public ?string $tenantId = null,
        #[WithCast(VariablesCaster::class, Variable::class)] // TODO: is this possible?
        public ?array $variables = [],
    ) {}
}
