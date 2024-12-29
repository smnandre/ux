<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Test;

use PHPUnit\Framework\Constraint;
use Symfony\UX\Map\Renderer\RendererInterface;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentRenderer;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\MountedComponent;

/**
 * @author Simon André <smn.andre@gmail.com>
 */
trait TwigComponentAssertionsTrait
{
    
    public function assertComponentHasClass(string $class, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentHasClass($class), $message);
    }
    
    public function assertComponentNotHasClass(string $class, string $message = ''): void
    {
        //
    }
    
    public function assertComponentClassSame(string $class, string $message = ''): void
    {
        //
    }
    
    public function assertComponentHasAttribute(string $name, string $message = ''): void
    {
        //
    }
    
    public function assertComponentNotHasAttribute(string $name, string $message = ''): void
    {
        //
    }
    
    public function assertComponentAttributeValueSame(string $name, string $value = null, string $message = ''): void
    {
        //
    }
    
    public function assertComponentAttributeValueNotSame(string $name, string $value = null, string $message = ''): void
    {
        //
    }
    
    public function assertComponentTextContentContains(string $text, string $message = ''): void
    {
        //
    }
    
    public function assertComponentTextContentNotContains(string $text, string $message = ''): void
    {
        //
    }
    
    public function assertComponentElementCount(string $selector, string $message = ''): void
    {
        //
    }
    
    public function assertComponentHasElement(string $selector, string $message = ''): void
    {
        //
    }
    
    public function assertComponentNotHasElement(string $selector, string $message = ''): void
    {
        //
    }
    
    public function assertComponentHtmlContains(string $text, string $message = ''): void
    {
        //
    }
    
    public function assertComponentHtmlNotContains(string $html, string $message = ''): void
    {
        //
    }
    
    public function assertComponentTemplateSame(string $template, string $message = ''): void
    {
        //
    }
    
    public function assertComponentTemplateNotSame(string $template, string $message = ''): void
    {
        //
    }
    
    public static function assertThatForComponent(Constraint $constraint, string $message = ''): void
    {
        $component = $this->getComponent();
        
        if (!$component instanceof ComponentInterface) {
            throw new \LogicException('The "assertThatForComponent" method can only be used on classes that implement "ComponentInterface".');
        }
        
        $this->assertThat($component, $constraint, $message);
    }
    
    private function getRenderer(): ComponentRendererInterface
    {
        if (!$this instanceof KernelTestCase) {
            throw new \LogicException('The "getRenderer" method can only be used on classes that implement "KernelTestCase".');
        }
        
        $inner = static::getContainer()->get('ux.twig_component.renderer');
        
        $mountedComponent = null; 
        
        $renderer = new class($inner) implements ComponentRendererInterface
        {
            private MountedComponent $component;
            
            public function __construct(
                private readonly ComponentRenderer $inner,
                private readonly ComponentFactory $factory,
            ) {
            }
            
            public function createAndRender(string $name, array $props = []): string
            {
                $this->component = $this->factory->create($name, $props);
                
                return $this->html = $this->inner->render($this->component);
            }
            
            public function getComponent(): string
            {
                return $this->inner->getComponent();
            }
        };
        
         static::getContainer()->get('ux.twig_component.renderer');
    }
    
    private function getComponent(): RenderedComponent
    {
        $html = $this->renderer->getComponent();
        //
        return new RenderedComponent($html);
    }
    
    abstract private function renderComponent(string $name, array $data = [], ?string $content = null, array $blocks = []): RenderedComponent;
    
}
