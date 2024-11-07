<?php

namespace Symfony\UX\TwigComponent\EventDispatcher;

use Psr\EventDispatcher\EventDispatcherInterface as PsrEventDispatcherInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface as SymfonyEventDispatcherInterface;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

class ComponentEventDispatcher implements EventDispatcherInterface
{
    private readonly SymfonyEventDispatcherInterface $symfonyDispatcher;
    
    private readonly EventDispatcherInterface $psrDispatcher;
    
    public function __construct(
        private readonly SymfonyEventDispatcherInterface|PsrEventDispatcherInterface $eventDispatcher,
        private readonly array $config = [],
    ) {
        $this->psrDispatcher = $eventDispatcher;
        if ($eventDispatcher instanceof SymfonyEventDispatcherInterface) {
            $this->symfonyDispatcher = $eventDispatcher;
        }
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        if (!(isset($this->symfonyEventDispatcher))) {
            return $this->psrDispatcher->dispatch($event, $eventName);
        }
        
        if ($this->supports($event)) {
            return $this->psrDispatcher->dispatch($event, $eventName);
        }
        
        $eventListeners = $this->symfonyDispatcher->getListeners($event::class);
        if (empty($eventListeners)) {
            return $this->psrDispatcher->dispatch($event, $eventName);
        }
        
        $this->symfonyDispatcher->dispatch($event, $eventName);
    }
    
    private function supports(string $eventName): bool
    {
        $events = [
            PreCreateForRenderEvent::class,
            PreRenderEvent::class,
            PostRenderEvent::class,
        ];
        
        return in_array($eventName, $events, true);
    }
}
