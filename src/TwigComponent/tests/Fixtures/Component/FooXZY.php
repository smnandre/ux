<?php
/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Tests\Fixtures\Component;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent(name: 'FooXZY', template:'dino/FooXYZ.html.twg')]
final class FooXZY
{
    // This component should have its template updated by the EventListener

    // 'dino/FooXYZ.html.twg' -> 'dino/FooXYZ.XYZ.html.twg'
}
