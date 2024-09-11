<?php

namespace Laravolt\Camunda\Dto\Casters;

use ArrayAccess;
use LogicException;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class VariablesCaster implements Cast
{
    public function __construct(
        private array $types,
        private string $itemType,
    ) {}

    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        foreach ($this->types as $type) {
            if ($type === 'array') {
                return $this->castArray($value);
            }

            if (is_subclass_of($type, ArrayAccess::class)) {
                return $this->castArrayAccess($value);
            }

            throw new LogicException(
                'Caster [ArrayCaster] may only be used to cast arrays or objects that implement ArrayAccess.'
            );
        }
    }

    private function castArray(mixed $value): array
    {
        return collect($value)
            ->map(fn(array $data, string $key) => $this->castItem($key, $data))
            ->toArray();
    }

    private function castArrayAccess(mixed $value): ArrayAccess
    {
        $arrayAccess = new $this->type(); // TODO: Undefined property '$type'.

        array_walk(
            $value,
            fn(array $data, string $key) => $arrayAccess[] = $this->castItem($key, $data)
        );

        return $arrayAccess;
    }

    private function castItem($key, $data)
    {
        return new $this->itemType(name: $key, type: $data['type'], value: $data['value']);
    }
}
