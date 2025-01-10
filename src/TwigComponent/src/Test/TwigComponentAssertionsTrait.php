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

use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalNot;
use Symfony\UX\TwigComponent\Test\Constraint as ComponentConstraint;

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
        $this->assertThatForComponent(new LogicalNot(new ComponentConstraint\ComponentHasClass($class)), $message);
    }

    public function assertComponentClassSame(string $class, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentClassSame($class), $message);
    }

    public function assertComponentHasAttribute(string $name, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentHasAttribute($name), $message);
    }

    public function assertComponentNotHasAttribute(string $name, string $message = ''): void
    {
        $this->assertThatForComponent(new LogicalNot(new ComponentConstraint\ComponentHasAttribute($name)), $message);
    }

    public function assertComponentAttributeValueSame(string $name, string $value = null, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentAttributeValueSame($name, $value), $message);
    }

    public function assertComponentAttributeValueNotSame(string $name, string $value = null, string $message = ''): void
    {
        $this->assertThatForComponent(new LogicalNot(new ComponentConstraint\ComponentAttributeValueSame($name, $value)), $message);
    }

    public function assertComponentTextContentContains(string $text, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentTextContains($text), $message);
    }

    public function assertComponentTextContentNotContains(string $text, string $message = ''): void
    {
        $this->assertThatForComponent(new LogicalNot(new ComponentConstraint\ComponentTextContains($text)), $message);
    }

    public function assertComponentElementCount(string $count, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentElementCount($count), $message);
    }

    public function assertComponentHasElement(string $selector, string $message = ''): void
    {
        self::assertThatForComponent(new ComponentConstraint\ComponentHasElement($selector), $message);
    }

    public function assertComponentNotHasElement(string $selector, string $message = ''): void
    {
        self::assertThatForComponent(new LogicalNot(new ComponentConstraint\ComponentHasElement($selector)), $message);
    }

    public function assertComponentHtmlContains(string $html, string $message = ''): void
    {
        $this->assertThatForComponent(new ComponentConstraint\ComponentTextContains($html), $message);
    }

    public function assertComponentHtmlNotContains(string $html, string $message = ''): void
    {
        $this->assertThatForComponent(new LogicalNot(new ComponentConstraint\ComponentTextContains($html)), $message);
    }

    // public function assertComponentTemplateSame(string $template, string $message = ''): void
    // {
    //     //
    // }
    //
    // public function assertComponentTemplateNotSame(string $template, string $message = ''): void
    // {
    //     //
    // }

    abstract protected static function getRenderedComponent(): ?RenderedComponent;

    public static function assertThatForComponent(Constraint $constraint, string $message = ''): void
    {
        $component = self::getRenderedComponent();

        if (!$component instanceof RenderedComponent) {
            throw new \LogicException('The "assertThatForComponent" method can only be used on classes that implement "ComponentInterface".');
        }

        self::assertThat($component, $constraint, $message);
    }

    // private function getRenderer(): ComponentRendererInterface
    // {
    //     if (!$this instanceof KernelTestCase) {
    //         throw new \LogicException('The "getRenderer" method can only be used on classes that implement "KernelTestCase".');
    //     }
    //
    //     $inner = static::getContainer()->get('ux.twig_component.renderer');
    //
    //     $mountedComponent = null;
    //
    //     $renderer = new class($inner) implements ComponentRendererInterface {
    //         private MountedComponent $component;
    //
    //         public function __construct(
    //             private readonly ComponentRenderer $inner,
    //             private readonly ComponentFactory $factory,
    //         )
    //         {
    //         }
    //
    //         public function createAndRender(string $name, array $props = []): string
    //         {
    //             $this->component = $this->factory->create($name, $props);
    //
    //             return $this->html = $this->inner->render($this->component);
    //         }
    //
    //         public function getComponent(): string
    //         {
    //             return $this->inner->getComponent();
    //         }
    //     };
    //
    //     static::getContainer()->get('ux.twig_component.renderer');
    // }

    // private function getComponent(): RenderedComponent
    // {
    //     $html = $this->renderer->getComponent();
    //
    //     //
    //     return new RenderedComponent($html);
    // }

    // abstract private function renderComponent(string $name, array $data = [], ?string $content = null, array $blocks = []): RenderedComponent;

}
