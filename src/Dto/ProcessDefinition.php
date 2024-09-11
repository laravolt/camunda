<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Spatie\LaravelData\Data;

class ProcessDefinition extends Data
{
    public function __construct(
        public string $id,
        public string $key,
        public string $category,
        public ?string $description,
        public ?string $name,
        public int $version,
        public string $resource,
        public string $deploymentId,
        public ?string $diagram,
        public bool $suspended,
        public ?string $tenantId,
        public ?string $versionTag,
        public ?string $historyTimeToLive,
        public bool $startableInTasklist,
    ) {}
}
