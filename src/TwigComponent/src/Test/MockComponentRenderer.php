<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Test;

use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\MountedComponent;

final class MockComponentRenderer implements ComponentRendererInterface
{
    public function createAndRender(string $name, array $props = []): string
    {
        // If the component is not found in the components array, return an empty string
        return '';
    }
}
