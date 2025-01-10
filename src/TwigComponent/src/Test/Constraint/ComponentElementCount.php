<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint as PhpUnitConstraint;
use Symfony\UX\TwigComponent\Test\RenderedComponent;

final class ComponentElementCount extends PhpUnitConstraint
{
    public function __construct(
        private readonly string $selector,
        private readonly int $count = 1,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('has "%s" elements "%s"', $this->count, $this->selector);
    }

    /**
     * @param RenderedComponent $component
     */
    protected function matches($component): bool
    {
        return  $this->count === $component->crawler()->filter($this->selector)->count();
    }
}
