<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Event;

use Symfony\Contracts\EventDispatcher\Event;
use Symfony\UX\TwigComponent\Component\Component;
use Symfony\UX\TwigComponent\ComponentMetadata;

/**
 * A generic event for all component-related events.
 * 
 * @author Simon André <smn.andre@gmail.com>
 */
class ComponentEvent extends Event
{
    public function __construct(
        private readonly Component $component,
    ) {
    }
    
    public function getName(): string
    {
        return $this->component->getName();
    }
    
    public function getProps(): array
    {
        return $this->component->getProps();
    }
    
    public function getMetadata(): ComponentMetadata
    {
        return $this->component->getMetadata();
    }
}
