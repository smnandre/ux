<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Map\Twig;

use Symfony\UX\Map\MapFactory;
use Symfony\UX\Map\Renderer\RendererInterface;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class UXMapComponentListener
{
    public function __construct(
        private RendererInterface $mapRenderer,
    ) {
    }

    public function onPreCreateForRender(PreCreateForRenderEvent $event): void
    {
        if ('ux:map' !== strtolower($event->getName())) {
            return;
        }

        $attributes = $event->getInputProps();
        $map = array_intersect_key($attributes, array_flip(['zoom', 'center', 'markers']));
        $attributes = array_diff_key($attributes, $map);

        $map = MapFactory::fromArray($map);

        $svg = $this->mapRenderer->renderMap($map, $attributes);
        $event->setRenderedString($svg);
        $event->stopPropagation();
    }
}
