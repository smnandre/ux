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
use Symfony\UX\TwigComponent\DataCollector\TwigComponentDataCollector;
use Symfony\UX\TwigComponent\EventListener\TwigComponentLoggerListener;
use Twig\Environment;

class TwigComponentDataCollectorTest extends TestCase
{
    public function testReset(): void
    {
        $logger = new TwigComponentLoggerListener(new Stopwatch());
        $twig = $this->createMock(Environment::class);
        $dataCollector = new TwigComponentDataCollector($logger, $twig);

        $dataCollector->lateCollect();
        $this->assertNotSame([], $dataCollector->getData());

        $dataCollector->reset();
        $this->assertSame([], $dataCollector->getData());
    }

    public function testGetName(): void
    {
        $logger = new TwigComponentLoggerListener(new Stopwatch());
        $twig = $this->createMock(Environment::class);
        $dataCollector = new TwigComponentDataCollector($logger, $twig);

        $this->assertEquals('twig_component', $dataCollector->getName());
    }
}
