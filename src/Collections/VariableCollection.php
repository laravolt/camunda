<?php

namespace Laravolt\Camunda\Collections;

use Illuminate\Support\Collection;
use Laravolt\Camunda\Dto\VariableType\BooleanType;
use Laravolt\Camunda\Dto\VariableType\JsonType;
use Laravolt\Camunda\Dto\VariableType\ObjectType;
use Laravolt\Camunda\Dto\VariableType\StringType;

class VariableCollection extends Collection
{
    public function toArray()
    {
        $variables = [];
        foreach ($this->items as $key => $value) {
            $valueType = gettype($value);
            $typeClass = match ($valueType) {
                'array' => JsonType::class,
                'boolean' => BooleanType::class,
                'object' => ObjectType::class,
                default => StringType::class,
            };

            $variables[$key] = (new $typeClass())($value);
        }

        return $variables;
    }
}
