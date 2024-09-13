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

final class ComponentEvent
{
    public function __construct(
        private string $componentName,
        private string $eventName,
        private ?object $event = null,
        private ?object $component = null,
    ) {
    }

    public function getEventName(): string
    {
        return $this->eventName;
    }

    public function getComponentName(): string
    {
        return $this->componentName;
    }

    public function getEvent(): ?object
    {
        return $this->event;
    }

    public function getComponent(): ?object
    {
        return $this->component;
    }
}
