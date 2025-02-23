<?php

namespace Spatie\LaravelData\Transformers;

interface Transformer
{
    public function canTransform(mixed $value): bool;

    public function transform(mixed $value): mixed;
}
