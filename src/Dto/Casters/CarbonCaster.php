<?php

namespace Laravolt\Camunda\Dto\Casters;

use Illuminate\Support\Carbon;
use Spatie\LaravelData\Casts\Cast;
use Spatie\LaravelData\Support\DataProperty;

class CarbonCaster implements Cast
{
    public function cast(DataProperty $property, mixed $value, array $context): mixed
    {
        return Carbon::parse($value);
    }
}
