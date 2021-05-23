<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class ProcessDefinition extends DataTransferObject
{
    public string $id;

    public string|null $tenantId;

    public string $key;

    public string $category;

    public ?string $name;

    public ?string $description;

    public int $version;

    public string $resource;

    public string $deploymentId;

    public ?string $diagram;

    public bool $suspended;

    public ?string $versionTag;

    public ?string $historyTimeToLive;

    public bool $startableInTasklist;
}
