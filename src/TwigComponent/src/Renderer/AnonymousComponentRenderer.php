<?php

namespace Symfony\UX\TwigComponent\Renderer;

use stdClass;
use Symfony\UX\TwigComponent\ComponentAttributes;
use Symfony\UX\TwigComponent\ComponentMetadata;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;
use Symfony\UX\TwigComponent\MountedComponent;
use Twig\Environment;

/**
 * 
 * @author Simon André <smn.andre@gmail.com>
 *     
 * @internal
 */
final class AnonymousComponentRenderer implements ComponentRendererInterface
{
    public function render(string $name, array $props = []): string
    {
        return 'anonymous' .$name;
    }
    

    public function startEmbeddedComponentRender(string $name, array $props, array $context, string $hostTemplateName, int $index)
    {
            return new PreRenderEvent(
                new MountedComponent($name, new stdClass(), $attr = new ComponentAttributes([])),
                new ComponentMetadata( [
                    'template' => 'components/'.$name.'.html.twig', 
                ]),
                [
                    'template' => $hostTemplateName,
                ...$context,
                ...$props,
                    'attributes' => $attr, 
                    ]
            );
    }
}
