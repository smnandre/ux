<?php

namespace Symfony\UX\TwigComponent\Tests\Unit\Escaper;

use PHPUnit\Framework\TestCase;
use Symfony\UX\TwigComponent\Escaper\TwigHtmlAttributeEscaper;
use Twig\Runtime\EscaperRuntime;

class TwigHtmlAttributeEscaperTest extends TestCase
{
    /**
     * @dataProvider nameProvider
     */
    public function testEscapeName(string $input, string $expected): void
    {
        $escaper = new TwigHtmlAttributeEscaper(new EscaperRuntime());
        $this->assertSame($expected, $escaper->escapeName($input));
    }

    /**
     * @dataProvider valueProvider
     */
    public function testEscapeValue(string $input, string $expected): void
    {
        $escaper = new TwigHtmlAttributeEscaper(new EscaperRuntime());
        $this->assertSame($expected, $escaper->escapeValue($input));
    }

    public static function nameProvider(): iterable
    {
        // Should not escape
        yield 'basic' => ['class', 'class'];
        yield 'data-' => ['data-user', 'data-user'];
        yield 'aria' => ['aria-label', 'aria-label'];
        yield 'xml' => ['xml:lang', 'xml:lang'];
        yield 'alnum' => ['attr123', 'attr123'];
        yield 'unicode' => ['data-🚀', 'data-🚀'];
        // Should escape
        yield 'scripts' => ['><script>alert(1)</script>', '&gt;&lt;script&gt;alert(1)&lt;/script&gt;'];
        yield 'single quote' => ["'", '&#039;'];
        yield 'double quote' => ['"', '&quot;'];
        yield 'ampersand' => ['&', '&amp;'];
        yield 'less than' => ['<', '&lt;'];
        yield 'greater than' => ['>', '&gt;'];
    }

    public static function valueProvider(): iterable
    {
        // Should not escape
        yield 'plain text' => ['Hello', 'Hello'];
        yield 'numeric value' => ['42', '42'];
        yield 'js url' => ['javascript:alert(1)', 'javascript:alert(1)'];

        // Should escape
        yield 'ampersand' => ['Hello & Welcome', 'Hello &amp; Welcome'];
        yield 'single quote' => ["O'Reilly", 'O&#039;Reilly'];
        yield 'double quotes' => ['"Hello"', '&quot;Hello&quot;'];
        yield 'less than' => ['<', '&lt;'];
        yield 'greater than' => ['>', '&gt;'];
        yield 'script' => ['<script>alert(1)</script>', '&lt;script&gt;alert(1)&lt;/script&gt;'];
        yield 'inline xss' => ['<img src=x onerror=alert(1)>', '&lt;img src=x onerror=alert(1)&gt;'];
        yield 'malicious attr' => ['foo="bar"', 'foo=&quot;bar&quot;'];
        yield 'sql injection' => ["' OR 1=1 --", '&#039; OR 1=1 --'];
        yield 'url encoded xss' => ['%3Cscript%3Ealert(1)%3C/script%3E', '%3Cscript%3Ealert(1)%3C/script%3E'];
    }
}
