<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\EventDispatcher;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\Icons\Twig\UXIconComponentListener;
use Symfony\UX\TwigComponent\Debug\ComponentEventLogger;
use Symfony\UX\TwigComponent\Event\PostMountEvent;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;
use Symfony\UX\TwigComponent\Event\PreMountEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

/**
 * @internal
 */
final class ComponentEventDispatcher implements EventDispatcherInterface
{
    public function __construct(
        private EventDispatcherInterface $dispatcher,
        private ComponentEventLogger $logger,
        private ?UXIconComponentListener $iconComponentListener = null,
    ) {
    }

    public function dispatch(object $event, ?string $eventName = null): object
    {
        if (!str_starts_with($event::class, 'Symfony\UX\TwigComponent\Event')) {
            throw new \InvalidArgumentException(\sprintf('The %s is restricted to Symfony\UX\TwigComponent\Event namespace, got "%s".', __CLASS__, $event::class));
        }

        $listeners = $this->dispatcher->getListeners($event::class);

        $listeners = array_filter($listeners, function ($listener) {
            return !str_contains(get_class($listener[0]), 'LiveComponent');
        });

        foreach ($listeners as $listener) {
            if (str_contains(get_class($listener[0]), 'IconComponentListener')) {
                if ('ux:icon' === strtolower($event->getName())) {
                    $listener($event);
                }
                continue;
            }
            if (str_contains(get_class($listener[0]), 'LiveComponent')) {
                continue;
            }
            $listener($event);
        }

        if ($event instanceof PreCreateForRenderEvent) {
            foreach ($listeners as $listener) {
                if (str_contains(get_class($listener[0]), 'IconComponentListener')) {
                    if ('ux:icon' === strtolower($event->getName())) {
                        $listener($event);
                    }
                    continue;
                }
                if (str_contains(get_class($listener[0]), 'LiveComponent')) {
                    continue;
                }
                $listener($event);
            }
            return $event;
        }

        if ($event instanceof PreMountEvent) {
            if (str_starts_with(strtolower($event->getMetadata()->getName()), 'ux:')) {
                $event->stopPropagation();

                return $event;
            }
        }

        if ($event instanceof PostMountEvent) {
            if (str_starts_with(strtolower($event->getMetadata()->getName()), 'ux:')) {
                $event->stopPropagation();

                return $event;
            }
        }

        //
        // if ($event instanceof PreRenderEvent) {
        //     if (str_starts_with(strtolower($event->getMetadata()->getName()), 'ux:icon')) {
        //         return $event;
        //     }
        //
        //     // Live?
        //     // Component not live?
        //     foreach ($this->dispatcher->getListeners($event::class) as $listener) {
        //         // $listener($event);
        //     }
        //
        //     return $event;
        // }

        if ($event instanceof PostMountEvent) {
            return $event;
        }

        $eventName = $eventName ?: $event::class;

        $this->logger->log($eventName, $event);

        foreach ($listeners as $listener) {
            $listener($event);
        }

        return $event;

        //return $this->dispatcher->dispatch($event, $eventName);
    }

    public function addListener(string $eventName, callable|array $listener, int $priority = 0): never
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }

    public function addSubscriber(EventSubscriberInterface $subscriber): never
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }

    public function removeListener(string $eventName, callable|array $listener): never
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }

    public function removeSubscriber(EventSubscriberInterface $subscriber): never
    {
        throw new \BadMethodCallException('Unmodifiable event dispatchers must not be modified.');
    }

    public function getListeners(?string $eventName = null): array
    {
        return $this->dispatcher->getListeners($eventName);
    }

    public function getListenerPriority(string $eventName, callable|array $listener): ?int
    {
        return $this->dispatcher->getListenerPriority($eventName, $listener);
    }

    public function hasListeners(?string $eventName = null): bool
    {
        return $this->dispatcher->hasListeners($eventName);
    }
}
