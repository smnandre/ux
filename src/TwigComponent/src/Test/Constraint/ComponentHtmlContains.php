<?php

namespace Symfony\UX\TwigComponent\Test\Constraint;

final class ComponentHtmlContains extends \PHPUnit\Framework\Constraint\Constraint
{
    public function __construct(
        private readonly string $string,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('HTML contains "%s"', $this->string);
    }
}
