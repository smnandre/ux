<?php

namespace Symfony\UX\TwigComponent\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

final class ComponentHasClass extends Constraint
{
    public function __construct(string $class)
    {
        
    }
    
    public function toString(): string
    {
        // TODO: Implement toString() method.
    }
}
