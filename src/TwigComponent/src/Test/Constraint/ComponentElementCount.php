<?php

namespace Symfony\UX\TwigComponent\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint as PhpUnitConstraint;

class ComponentElementCount extends PhpUnitConstraint
{
    /**
     * @param string $count
     */
    public function __construct(string $count)
    {
    }
    
    public function toString(): string
    {
        // TODO: Implement toString() method.
    }
}
