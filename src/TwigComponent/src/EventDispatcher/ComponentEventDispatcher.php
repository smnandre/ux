<?php

namespace Symfony\UX\TwigComponent\EventDispatcher;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

final class ComponentEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private readonly ?EventDispatcherInterface $inner = null,
    )  {
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        // $event->setDispatcher($this);
        
        // component event ? dd
        // componentEventInterface
        
        $eventName ??= get_class($event).'::'.$event->getName();
        
        
        return $this->inner->dispatch($event, $eventName);
        
        
    }
}
