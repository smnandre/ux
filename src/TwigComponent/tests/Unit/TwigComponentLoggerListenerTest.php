<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Symfony\UX\TwigComponent\ComponentMetadata;
use Symfony\UX\TwigComponent\Event\PostMountEvent;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;
use Symfony\UX\TwigComponent\Event\PreMountEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;
use Symfony\UX\TwigComponent\EventListener\TwigComponentLoggerListener;
use Symfony\UX\TwigComponent\MountedComponent;

class TwigComponentLoggerListenerTest extends TestCase
{
    public function testLoggerStoreEvents(): void
    {
        $stopwatch = $this->createMock(Stopwatch::class);
        $logger = new TwigComponentLoggerListener($stopwatch);
        $this->assertSame([], $logger->getEvents());

        $eventA = new PreCreateForRenderEvent('a');
        $logger->onPreCreateForRender($eventA);
        $this->assertSame([$eventA], array_column($logger->getEvents(), 0));

        $eventB = new PreCreateForRenderEvent('b');
        $logger->onPreCreateForRender($eventB);
        $this->assertSame([$eventA, $eventB], array_column($logger->getEvents(), 0));

        $eventC = new PreMountEvent(new \stdClass(), []);
        $logger->onPreMount($eventC);
        $eventD = new PostMountEvent(new \stdClass(), []);
        $logger->onPostMount($eventD);
        $this->assertSame([$eventA, $eventB, $eventC, $eventD], array_column($logger->getEvents(), 0));

        $mounted = new MountedComponent('foo', new \stdClass(), new ComponentAttributes([]));
        $eventE = new PreRenderEvent($mounted, new ComponentMetadata(['template' => 'bar']), []);
        $logger->onPreRender($eventE);
        $eventF = new PostRenderEvent($mounted);
        $logger->onPostRender($eventF);
        $this->assertSame([$eventA, $eventB, $eventC, $eventD, $eventE, $eventF], array_column($logger->getEvents(), 0));
    }

    public function testLoggerReset(): void
    {
        $stopwatch = $this->createMock(Stopwatch::class);
        $logger = new TwigComponentLoggerListener($stopwatch);

        $logger->onPreCreateForRender(new PreCreateForRenderEvent('foo'));
        $this->assertNotSame([], $logger->getEvents());

        $logger->reset();
        $this->assertSame([], $logger->getEvents());
    }
}
