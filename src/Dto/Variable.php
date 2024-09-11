<?php

namespace Laravolt\Camunda\Dto;

use Spatie\LaravelData\Data;

class Variable extends Data
{
    public function __construct(
        public string $name,
        public string $type,
        public mixed $value,
        public ?array $valueInfo = [],
    ) {}
}
