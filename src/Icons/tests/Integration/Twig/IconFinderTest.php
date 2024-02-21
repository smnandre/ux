<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Tests\Integration\Twig;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\Icons\Twig\NodeVisitor\IconExtractorNodeVisitor;
use Twig\Environment;

class IconFinderTest extends KernelTestCase
{
    public function testIcons()
    {
        /** @var Environment $twig */
        $twig = self::getContainer()->get('twig');

        $visitor = new IconExtractorNodeVisitor();
        $twig->addNodeVisitor($visitor);


        $twig->load('template1.html.twig');

        dump($visitor->getIcons());
    }
}
