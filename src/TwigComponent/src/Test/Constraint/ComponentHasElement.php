<?php

namespace Symfony\UX\TwigComponent\Test\Constraint;

final class ComponentHasElement extends \PHPUnit\Framework\Constraint\Constraint
{
    public function __construct(
        private readonly string $selector,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('has element "%s"', $this->name);
    }
}
