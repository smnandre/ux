<?php

namespace Symfony\UX\Html\Attribute;

class Attribute
{
    public function __construct(
        public readonly string $attribute,
        public readonly string $value,
        public readonly string $type,
    ) {
    }
    
    public function apply(string $tag): bool
    {
    }
}
