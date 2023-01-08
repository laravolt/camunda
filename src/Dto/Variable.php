<?php

namespace Laravolt\Camunda\Dto;

use Spatie\DataTransferObject\Attributes\Strict;
use Spatie\DataTransferObject\DataTransferObject;

#[Strict]
class Variable extends DataTransferObject
{
    public string $name;

    public string $type;

    public mixed $value;

    public array $valueInfo = [];
}
