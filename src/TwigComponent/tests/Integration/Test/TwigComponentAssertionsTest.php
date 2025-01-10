<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Integration\Test;

use PHPUnit\Framework\Constraint\Constraint;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\TwigComponent\Test\Constraint\ComponentHasClass;
use Symfony\UX\TwigComponent\Test\Constraint\ComponentHtmlContains;
use Symfony\UX\TwigComponent\Test\RenderedComponent;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
final class TwigComponentAssertionsTest extends KernelTestCase
{
    /**
     * @dataProvider provideAssertionCases
     */
    public function testAssertions(bool $expected, string $html, Constraint $constraint): void
    {
        $component = new RenderedComponent($html);

        self::assertSame($expected, $constraint->evaluate($component, '', true));
    }

    public static function provideAssertionCases(): iterable
    {
        yield [true, '<div class="foo"></div>', new ComponentHasClass('foo')];
        yield [true, '<div class="foo bar"></div>', new ComponentHasClass('foo')];
        // yield [true, '<div class="bar foo"></div>', new ComponentHasClass('foo')];
        // yield [true, '<div class="bar foo bar"></div>', new ComponentHasClass('foo')];
        yield [true, '<div class="foo bar"></div>', new ComponentHtmlContains('foo')];
        yield [true, '<div>foo</div>', new ComponentHtmlContains('foo')];
        yield [true, '<div><span>foo</span></div>', new ComponentHtmlContains('foo')];
        // yield [false, '<div class="foo"></div>', new ComponentHasClass('bar')];
        // yield [false, '<div class="foo"></div>', new ComponentHasClass('foobar')];
        // yield [false, '<div class="foo"></div>', new ComponentHasClass('barfoo')];
        //
        // yield [true, '<div class="foo"></div>', new ComponentElementCount('div', 1)];
        // yield [true, '<div class="foo"></div><div class="foo"></div>', new ComponentElementCount('div', 2)];
        // yield [false, '<div class="foo"></div>', new ComponentElementCount('div', 2)];
        // yield [false, '<div class="foo"></div><div class="foo"></div>', new ComponentElementCount('div', 1)];
    }

    protected static function getRenderedComponent(): ?RenderedComponent
    {
        return null;
    }

    private function renderComponent(string $name, array $data = [], ?string $content = null, array $blocks = []): RenderedComponent
    {
        // TODO: Implement renderComponent() method.
    }
}
