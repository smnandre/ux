<?php

namespace Symfony\UX\TwigComponent\Renderer;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;
use Twig\Environment;

/**
 * 
 * @author Simon André <smn.andre@gmail.com>
 *     
 * @internal
 */
final class LegacyComponentRenderer implements ComponentRendererInterface
{
    public function __construct(
        private readonly ComponentRendererInterface $inner,
        private readonly ?EventDispatcherInterface $dispatcher = null,
    ) {
    }
    
    public function preCreateForRender(string $name, array $props = []): ?string
    {
        $event = new PreCreateForRenderEvent($name, $props);
        $this->dispatcher->dispatch($event);
        
        return $event->getRenderedString();
    }
    
    public function render(string $name, array $props = []): string
    {
        $this->dispatcher->dispatch(new PreRenderEvent($name, $props));
        
        $html = $this->inner->render($name, $props);
        
        $this->dispatcher->dispatch(new PostRenderEvent($name, $props));
    }
    
    public function embed(string $name, array $props = []): string
    {
        return '';
    }
}
