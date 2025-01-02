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
use Symfony\Component\HttpFoundation\Request;
use Symfony\UX\TwigComponent\Test\RenderedComponent;

final class ComponentClassSame extends PHPUnitConstraint
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function toString(): string
    {
        return \sprintf('class is same as "%s"', $this->value);
    }

    /**
     * @param RenderedComponent $component
     */
    protected function matches($component): bool
    {
        return $this->value === (string) $component->crawler()->first()->attr('class');
    }

    /**
     * @param Request $request
     */
    protected function failureDescription($request): string
    {
        return 'the Request '.$this->toString();
    }
}
