<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

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
        public ?array $variables = [],
    ) {}
}
