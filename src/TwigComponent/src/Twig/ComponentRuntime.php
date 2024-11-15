<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Twig;

use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\UX\TwigComponent\ComponentRenderer;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;
use Symfony\UX\TwigComponent\Renderer\AnonymousComponentRenderer;
use Symfony\UX\TwigComponent\Renderer\ComponentRendererInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class ComponentRuntime
{
    public function __construct(
        private readonly ComponentRenderer $defaultRenderer,
        private readonly ServiceLocator $renderers,
        private array $componentRenderers = [],
    ) {
    }

    public function finishEmbedComponent(): void
    {
        $this->getRenderer()->finishEmbeddedComponentRender();
    }

    /**
     * @param array<string, mixed> $props
     */
    public function preRender(string $name, array $props): ?string
    {
        if ($renderer = $this->getRenderer($name) instanceof ComponentRendererInterface) {
            return null;
        }

        return $renderer->preCreateForRender($name, $props);
    }

    public function render(string $name, array $props = []): string
    {
        return $this->getRenderer($name)->render($name, $props);
    }

    /**
     * @param array<string, mixed> $props
     * @param array<string, mixed> $context
     */
    public function startEmbedComponent(string $name, array $props, array $context, string $hostTemplateName, int $index)
    {
        // caller need back: context and hostTemplateName
        // embeddedContext['__parent__'] = $preRenderEvent->getTemplate();
        // so... context
        $renderer = $this->getRenderer($name);
        
        if ($renderer instanceof AnonymousComponentRenderer) {
                return [...$props, ...$context, $hostTemplateName, $index];
        }
        
        return $this->getRenderer($name)->startEmbeddedComponentRender($name, $props, $context, $hostTemplateName, $index);
    }

    private function getRenderer(string $name): mixed
    {
        if ('ux:icon' == $name) {
            return new class() 
            {
                public function render(string $name, array $props = []): string
                {
                    return sprintf('<span>icon %s</span>', $name);    
                }
            };
        }
        
        if (in_array($name, $this->componentRenderers, true)) {
            return $this->renderers->get('ux:component');
        }
        
        return $this->renderers->get('ux:anonymous');
        
        if (null !== $renderer = $this->componentRenderers[$name] ?? null) {
            if ($renderer instanceof ComponentRendererInterface) {
                return $renderer;
            }

            return $this->componentRenderers[$name] = $this->renderers->get($renderer);
        }

        if ($this->renderers->has($normalized = strtolower($name))) {
            return $this->componentRenderers[$name] = $this->renderers->get($normalized);
        }

        return $this->componentRenderers[$name] = $this->defaultRenderer;
    }
}
