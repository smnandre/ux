<?php

namespace Symfony\UX\TwigComponent\Renderer;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Service\ResetInterface;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\EventListener\TwigComponentLoggerListener;
use Twig\Environment;

/**
 * 
 * @author Simon André <smn.andre@gmail.com>
 *     
 * @internal
 */
final class TraceableComponentRenderer implements ComponentRendererInterface, ResetInterface
{
    private array $renders = [];
    
    public function __construct(
        private readonly ComponentRendererInterface $inner,
    ) {
    }
    
    public function render(string $name, array $props = []): string
    {
        $this->renders[] = $name;
        
        $start = microtime(true);
     
        $result = $this->inner->render($name, $props);
        
        $end = microtime(true);
        
        return $result;
    }

    public function reset(): void
    {
        $this->renders = [];
    }
}
