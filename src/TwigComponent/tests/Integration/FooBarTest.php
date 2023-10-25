<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Integration;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

final class FooBarTest extends KernelTestCase
{
    /**
     * @dataProvider getProblems
     */
    public function testProblems(string $template, string $expectedHtml): void
    {
        $environment = self::createEnvironment();
        $renderedHtml = $environment->render($template);

        self::assertSame($expectedHtml, trim($renderedHtml));
    }

    public static function getProblems(): iterable
    {
        return [
            // Anonymous components
            ['dino/anonymous_html.html.twig', 'ddd'],
            ['dino/anonymous_html_self.html.twig', 'ddd'],
            ['dino/anonymous_tag.html.twig', 'ddd'],
            // Anonymous components // templates updated by PreRenderEvent listener
            ['dino/anonymous_event_html.html.twig', 'ddd'],
            ['dino/anonymous_event_html_self.html.twig', 'ddd'],
            ['dino/anonymous_event_tag.html.twig', 'ddd'],
            // Class based components // templates updated by PreRenderEvent listener
            ['dino/event_html.html.twig', 'ddd'],
            ['dino/event_html_self.html.twig', 'ddd'],
            ['dino/event_tag.html.twig', 'ddd'],
        ];
    }

    private function createEnvironment(): Environment
    {
        return self::getContainer()->get(Environment::class);
    }
}
