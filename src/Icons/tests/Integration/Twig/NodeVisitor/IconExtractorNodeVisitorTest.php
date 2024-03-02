<?php

namespace Symfony\UX\Icons\Tests\Integration\Twig\NodeVisitor;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\Icons\Twig\NodeVisitor\IconExtractorNodeVisitor;
use Symfony\UX\Icons\Twig\UXIconExtension;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class IconExtractorNodeVisitorTest extends KernelTestCase
{
    public function testVisitorInstance(): void
    {
        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');
        $visitor = $twig->getExtension(UXIconExtension::class)->getNodeVisitors()[0];

        $this->assertInstanceOf(IconExtractorNodeVisitor::class, $visitor);
        $this->assertEquals([], $visitor->getIcons());
    }

    /**
     * @dataProvider provideExtractIconsData
     */
    public function testVisitorExtractIcons(string $template, array $icons): void
    {
        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');

        $visitor = $twig->getExtension(UXIconExtension::class)->getNodeVisitors()[0];
        $visitor->enable();

        $templateName = sprintf('template_%s.html.twig', hash('crc32', $template));
        $twig->setLoader(new ArrayLoader([
            $templateName => $template,
        ]));
        $twig->load($templateName);

        $visitorIcons = $visitor->getIcons();

        $this->assertNotEmpty($visitorIcons);
        $this->assertCount(count($icons), $visitorIcons);
        $this->assertEquals(array_column($visitorIcons, 'iconName'), $icons);

        $visitor->disable();
    }

    public static function provideExtractIconsData(): iterable
    {
        yield from [
            'twig_component' =>  [
                '{{ ux_icon("foo") }}',
                ['foo'],
            ],
            'twig_component_with_prefix' =>  [
                '{{ ux_icon("foo:bar") }}',
                ['foo:bar'],
            ],
            'twig_component_with_variable' =>  [
                "{% set foo = 'bar' %}\n{{ ux_icon(foo) }}",
                ['foo'],
            ],
             'twig_component_with_expression' =>  [
                "AA\n{{ ux_icon(2 < 4 ? 'foo' : 'bar') }}",
                [null], // Unable to extract icon name from expression
            ],
             'html_component' =>  [
                'AA <twig:UX:Icon foo="bar" name="foo" /> BB',
                ['foo'],
            ],
            'html_component_with_prefix' =>  [
                'AA <twig:UX:Icon name="foo:bar" /> BB',
                ['foo:bar'],
            ],
        ];
    }
}
