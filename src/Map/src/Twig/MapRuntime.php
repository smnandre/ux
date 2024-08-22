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

use Symfony\UX\Map\Map;
use Symfony\UX\Map\MapFactory;
use Symfony\UX\Map\Renderer\RendererInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class MapRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly RendererInterface $renderer,
    ) {
    }

    /**
     * @param Map|array<string, mixed> $map
     * @param array<string, mixed>     $attributes
     */
    public function renderMap(Map|array $map, array $attributes = []): string
    {
        if (\is_array($map)) {
            $map = MapFactory::fromArray($map);
        }

        return $this->renderer->renderMap($map, $attributes);
    }
}
