<?php

namespace Symfony\UX\TwigComponent\Test\Constraint;

final class ComponentHasAttribute extends \PHPUnit\Framework\Constraint\Constraint
{
    public function __construct(
        private readonly string $name,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('has attribute "%s"', $this->name);
    }
}
