<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Twig;

use Psr\Log\LoggerInterface;
use Symfony\UX\Icons\Exception\IconNotFoundException;
use Symfony\UX\Icons\IconRendererInterface;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class UXIconRuntime implements RuntimeExtensionInterface
{
    public function __construct(
        private readonly IconRendererInterface $iconRenderer,
        private readonly bool $ignoreNotFound = false,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * @param array<string, bool|string> $attributes
     */
    public function renderIcon(string $name, array $attributes = []): string
    {
        try {
            return $this->iconRenderer->renderIcon($name, $attributes);
        } catch (IconNotFoundException $e) {
            $this->logger?->warning($e->getMessage());
            if (!$this->ignoreNotFound) {
                throw $e;
            }
        }
    }

    public function renderSprite(array $names): string
    {
        try {
            return $this->iconRenderer->renderSprite($names);
        } catch (IconNotFoundException $e) {
            $this->logger?->warning($e->getMessage());
            if (!$this->ignoreNotFound) {
                throw $e;
            }
        }
    }

    public function renderSymbol(string $name): string
    {
        try {
            return $this->iconRenderer->renderSymbol($name);
        } catch (IconNotFoundException $e) {
            $this->logger?->warning($e->getMessage());
            if (!$this->ignoreNotFound) {
                throw $e;
            }
        }
    }

    public function render(array $args = []): string
    {
        $name = $args['name'];
        unset($args['name']);

        return $this->renderIcon($name, $args);
    }
}
