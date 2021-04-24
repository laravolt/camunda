<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class ProcessInstance extends DataTransferObject
{
    public string $id;

    public string|null $tenantId;

    public string $businessKey;

    public array $links;

    public string $definitionId;

    public ?string $caseInstanceId;

    public bool $ended;

    public bool $suspended;
}
