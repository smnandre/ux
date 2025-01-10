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

use PHPUnit\Framework\Constraint\Constraint as PHPUnitConstraint;
use Symfony\UX\TwigComponent\Test\RenderedComponent;

final class ComponentHasAttribute extends PHPUnitConstraint
{
    public function __construct(
        private readonly string $name,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('has attribute "%s"', $this->name);
    }

    /**
     * @param RenderedComponent $component
     */
    protected function matches($component): bool
    {
        return null !== $component->crawler()->attr($this->name);
    }
}
