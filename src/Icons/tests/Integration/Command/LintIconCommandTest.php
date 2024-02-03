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
 * @author Simon André <smn.andre@gmail.com>
 */
final class LintIconCommandTest extends KernelTestCase
{
    use InteractsWithConsole;

    public function testLintIcon(): void
    {
        $this->executeConsoleCommand('ux:icons:lint')
            ->assertSuccessful()
            ->assertOutputContains('[OK] All icons are valid (')
        ;
    }

    public function testLintIconWithErrors(): void
    {
        $path = realpath(__DIR__ . '/../../Fixtures/svg');
        $this->executeConsoleCommand('ux:icons:lint '.$path)
            ->assertStatusCode(1)
            ->assertOutputContains('Some icons contain errors (4 / 9)');
        ;
    }
}
