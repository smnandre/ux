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

final class ComponentHasClass extends PhpUnitConstraint
{
    public function __construct(
        private readonly string $class,
    )
    {
    }

    public function toString(): string
    {
        return \sprintf('has the class "%s"', $this->class);
    }

    /**
     * @param RenderedComponent $component
     */
    protected function matches($component): bool
    {
        dd($component->crawler()->filter('body > div')->attr('class'));
        return str_contains($component->attr('class'), $this->class);
    }
}
