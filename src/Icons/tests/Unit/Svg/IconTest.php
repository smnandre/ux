<?php

namespace Symfony\UX\Icons\Tests\Unit\Svg;

use PHPUnit\Framework\TestCase;
use Symfony\UX\Icons\Svg\Icon;

class IconTest extends TestCase
{
    public function testConstructor()
    {
        $icon = new Icon('foo', ['foo' => 'bar']);
        $this->assertSame('foo', $icon->getInnerSvg());
        $this->assertSame('bar', $icon['foo']);
    }
}
