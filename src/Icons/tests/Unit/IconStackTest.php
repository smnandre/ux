<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Tests\Unit;

use PHPUnit\Framework\TestCase;
use Symfony\UX\Icons\IconStack;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IconStackTest extends TestCase
{
    public function testCanAddItemsToStack(): void
    {
        $stack = new IconStack();
        $stack->push('foo');
        $stack->push('foo');
        $stack->push('bar');

        $this->assertSame(['foo', 'bar'], iterator_to_array($stack));
    }

    public function testCanReset(): void
    {
        $stack = new IconStack();
        $stack->push('foo');
        $stack->push('foo');
        $stack->push('bar');

        $stack->reset();

        $this->assertSame([], iterator_to_array($stack));
    }

    public function testResetsAfterIterating(): void
    {
        $stack = new IconStack();
        $stack->push('foo');
        $stack->push('foo');
        $stack->push('bar');

        $this->assertSame(['foo', 'bar'], iterator_to_array($stack));
        $this->assertSame([], iterator_to_array($stack));
    }
}
