<?php

namespace Laravolt\Camunda\Dto;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class Tenant extends DataTransferObject
{
    public string $id;

    public string $name;
}
