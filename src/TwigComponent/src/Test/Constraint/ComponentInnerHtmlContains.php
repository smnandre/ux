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

use Symfony\UX\TwigComponent\Test\RenderedComponent;

final class ComponentInnerHtmlContains extends Constraint
{
    public function __construct(
        private readonly string $string,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('inner HTML contains "%s"', $this->string);
    }

    /**
     * @param RenderedComponent $component
     */
    protected function matches($component): bool
    {
        return str_contains($component->crawler()->html(), $this->string);
    }
}
