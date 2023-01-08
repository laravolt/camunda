<?php

declare(strict_types=1);

namespace Laravolt\Camunda\Dto\VariableType;

class StringType
{
    public function __invoke($value): array
    {
        return ['value' => (string) $value, 'type' => 'String'];
    }
}
