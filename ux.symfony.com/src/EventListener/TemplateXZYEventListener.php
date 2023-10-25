<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

#[AsEventListener]
class TemplateXZYEventListener
{
    public function __invoke(PreRenderEvent $event)
    {
        if (str_ends_with($event->getMetadata()->getName(), 'XYZ')) {
            $event->setTemplate(str_replace('XYZ', 'XYZ.xyz', $event->getTemplate()));
        }
    }
}
