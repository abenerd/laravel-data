<?php

namespace Spatie\LaravelData\Casts;

use ReflectionNamedType;

interface Cast
{
    public function cast(ReflectionNamedType $reflectionType, mixed $value): mixed;
}
