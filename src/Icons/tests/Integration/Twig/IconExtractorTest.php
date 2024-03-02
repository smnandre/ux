<?php

namespace Symfony\UX\Icons\Tests\Integration\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\Icons\Twig\IconExtractor;
use Symfony\UX\Icons\Twig\NodeVisitor\IconExtractorNodeVisitor;
use Symfony\UX\Icons\Twig\UXIconExtension;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

class IconExtractorTest extends KernelTestCase
{
    public function testIconExtractorInstance(): void
    {
        $extractor = self::getContainer()->get('.ux_icons.twig_icon_extractor');
        $this->assertInstanceOf(IconExtractor::class, $extractor);
    }

    public function testIconExtrator(): void
    {
        $extractor = self::getContainer()->get('.ux_icons.twig_icon_extractor');

        $icons = $extractor->extractIcons();

        $this->assertNotEmpty($icons);
    }
}
