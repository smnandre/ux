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
use Symfony\UX\TwigComponent\ComponentFactory;
use Twig\Environment;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ComponentFooTest extends KernelTestCase
{
    /**
     * @dataProvider provideRenderBundleComponentsTests
     */
    public function testRenderBundleComponents(string $template, string $expected): void
    {
        $twig = self::getContainer()->get(Environment::class);

        self::assertSame($expected, trim($twig->render($template)));
    }


    public static function provideRenderBundleComponentsTests(): iterable
    {
        return [
            [
                'bundles/acme_blog_foo.html.twig',
                'AcmeBlog Foo'
            ],
            [
                'bundles/acme_blog_foo_bar.html.twig',
                "AcmeBlog FooBar\n\nAcmeBlog Foo\n\nAcmeBlog Foo"
            ],
            [
                'bundles/acme_form_foo.html.twig',
                'AcmeForm Foo'
            ],
            [
                'bundles/acme_form_bar.html.twig',
                'AcmeForm Bar'
            ],
            [
                'bundles/acme_form_foo_bar.html.twig',
                "AcmeForm FooBar\n\nAcmeForm Foo\n\nAcmeForm Bar"
            ],
        ];
    }

    private function factory(): ComponentFactory
    {
        return self::getContainer()->get('ux.twig_component.component_factory');
    }
}
