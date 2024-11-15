<?php

namespace Symfony\UX\TwigComponent\Renderer;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\TwigComponent\Attribute\PostMount;
use Symfony\UX\TwigComponent\Attribute\PreMount;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\Event\PostMountEvent;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;
use Symfony\UX\TwigComponent\Event\PreMountEvent;
use Twig\Environment;

/**
 * 
 * @author Simon André <smn.andre@gmail.com>
 *     
 * @internal
 */
final class ComponentRenderer implements ComponentRendererInterface
{
    private array $templateClasses = [];
    
    public function __construct(
        private readonly ComponentFactory $factory,
        private readonly Environment $twig,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }
    
    public function render(string $name, array $props = []): string
    {
        $component = $this->factory->create($name, $props);
        
        $this->dispatcher?->dispatch( new PreMountEvent($component, 'component.$name.pre_mount'));
        
        $variables = $component->getVariables();
        
        $this->dispatcher?->dispatch( new PostMountEvent($component, 'component.$name.pre_mount'));
        
        $this->dispatcher?->dispatch( new PostMountEvent($component, 'component.$name.pre_render'));
        
        // todo 'template"
        $template = $component->getTemplate();
        $templateIndex = $component->getTemplateIndex();
        
            return $this->twig->loadTemplate(
                $this->templateClasses[$template] ??= $this->twig->getTemplateClass($template),
                $template,
                $templateIndex,
            )->render($variables);
    }
    
    public function embed(string $name, array $props = []): string
    {
        return '';
    }
}
