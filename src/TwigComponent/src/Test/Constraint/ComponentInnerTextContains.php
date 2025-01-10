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

final class ComponentInnerTextContains extends PhpUnitConstraint
{
    public function __construct(
        private readonly string $text,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('inner text contains "%s"', $this->text);
    }

    /**
     * @param RenderedComponent $component
     */
    protected function matches($component): bool
    {
        return str_contains($component->crawler()->innerText(), $this->text);
    }
}
