<?php

namespace Symfony\UX\TwigComponent\Test\Constraint;

final class ComponentTextContains extends \PHPUnit\Framework\Constraint\Constraint
{
    public function __construct(
        private readonly string $text,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('text contains "%s"', $this->test);
    }
}
