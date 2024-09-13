<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Debug;

use Countable;
use IteratorAggregate;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\UX\TwigComponent\Event\ComponentEvent;

/**
 * @extends IteratorAggregate<int, ComponentEvent>
 * @extends Countable<ComponentEvent>
 *
 * @internal
 */
final class ComponentEventLogger implements \IteratorAggregate, \Countable, ResetInterface
{
    public function __construct(
        private array $events = [],
    ) {
    }

    public function log(string $eventName, ?object $event = null): void
    {
        // $this->events[] = [
        //     'eventName' => $eventName,
        //     'event' => $event,
        // ];
        $this->events[] = [$event::class];
        // $this->logEvent($event);
    }

    private function logEvent(object $event): void
    {
        $this->events[] = [$event, [microtime(true), memory_get_usage(true)]];
    }

    public function count(): int
    {
        return \count($this->events);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->events);
    }

    public function reset(): void
    {
        $this->events = [];
    }
}
