<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Spatie\DataTransferObject\DataTransferObject;

class ProcessDefinition extends DataTransferObject
{
    public string $key;
    public string $category;
    public ?string $name;
    public ?string $description;
    public int $version;
    public string $resource;
    public string $deploymentId;
    public ?string $diagram;
    public bool $suspended;
    public ?string $tenantId;
    public ?string $versionTag;
    public ?string $historyTimeToLive;
    public bool $startableInTasklist;
}
