<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Tests\Integration\Command;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Console\Test\InteractsWithConsole;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class ListIconsCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    public function testListLocalSvgIcons(): void
    {
        $this->executeConsoleCommand('ux:icons:list')
            ->assertSuccessful()
            ->assertOutputContains('Local SVG Icons')
            ->assertOutputContains('user')
            ->assertOutputContains('sub/check')
        ;
    }
}
