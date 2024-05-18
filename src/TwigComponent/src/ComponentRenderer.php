<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent;

use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreCreateForRenderEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;
use Symfony\UX\TwigComponent\Metadata\ComponentPropMetadata;
use Symfony\UX\TwigComponent\Metadata\ComponentPropsMetadataFactory;
use Twig\Environment;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ComponentRenderer implements ComponentRendererInterface
{
    private readonly ComponentPropsMetadataFactory $propsMetadataFactory;
    public function __construct(
        private Environment $twig,
        private EventDispatcherInterface $dispatcher,
        private ComponentFactory $factory,
        private PropertyAccessorInterface $propertyAccessor,
        private ComponentStack $componentStack,
        private ?ComponentPropMetadata $propMetadata = null,
    ) {
        $this->propsMetadataFactory = new ComponentPropsMetadataFactory();
    }

    /**
     * Allow the render process to be short-circuited.
     */
    public function preCreateForRender(string $name, array $props = []): ?string
    {
        $event = new PreCreateForRenderEvent($name, $props);
        $this->dispatcher->dispatch($event);

        return $event->getRenderedString();
    }

    public function createAndRender(string $name, array $props = []): string
    {
        if ($preRendered = $this->preCreateForRender($name, $props)) {
            return $preRendered;
        }

        return $this->render($this->factory->create($name, $props));
    }

    public function render(MountedComponent $mounted): string
    {
        $this->componentStack->push($mounted);

        $event = $this->preRender($mounted);

        $variables = $event->getVariables();
        // see ComponentNode. When rendering an individual embedded component,
        // *not* through its parent, we need to set the parent template.
        if ($event->getTemplateIndex()) {
            $variables['__parent__'] = $event->getParentTemplateForEmbedded();
        }

        try {
            return $this->twig->loadTemplate(
                $this->twig->getTemplateClass($event->getTemplate()),
                $event->getTemplate(),
                $event->getTemplateIndex(),
            )->render($variables);
        } finally {
            $mounted = $this->componentStack->pop();

            $event = new PostRenderEvent($mounted);
            $this->dispatcher->dispatch($event);
        }
    }

    public function startEmbeddedComponentRender(string $name, array $props, array $context, string $hostTemplateName, int $index): PreRenderEvent
    {
        $context[PreRenderEvent::EMBEDDED] = true;

        $mounted = $this->factory->create($name, $props);
        $mounted->addExtraMetadata('hostTemplate', $hostTemplateName);
        $mounted->addExtraMetadata('embeddedTemplateIndex', $index);

        $this->componentStack->push($mounted);

        return $this->preRender($mounted, $context);
    }

    public function finishEmbeddedComponentRender(): void
    {
        $mounted = $this->componentStack->pop();

        $event = new PostRenderEvent($mounted);
        $this->dispatcher->dispatch($event);
    }

    private function preRender(MountedComponent $mounted, array $context = []): PreRenderEvent
    {
        $component = $mounted->getComponent();
        $metadata = $this->factory->metadataFor($mounted->getName());

        $propsMetadata = $this->propsMetadataFactory->create($component::class);

        $exposedInTemplate = [];
        foreach ($propsMetadata as $propMetadata) {
            if ($propMetadata->method) {
                $value = $component->{$propMetadata->method}();
            } else {
                $value = $component->{$propMetadata->name};
            }
            $exposedInTemplate[$propMetadata->name] = $value;
        }

        // expose public properties and properties marked with ExposeInTemplate attribute
        $props = $exposedInTemplate;
        $variables = [
            // first so values can be overridden
            $context,
            // keep reference to old context
            'outerScope' => $context,
            // add the component as "this"
            'this' => $component,
            // add computed properties proxy
            'computed' => new ComputedPropertiesProxy($component),
            // add attributes
            $metadata->getAttributesVar() => $mounted->getAttributes(),
            ...$props,
            ['__props' => $props],
        ];
        $event = new PreRenderEvent($mounted, $metadata, $variables);

        $this->dispatcher->dispatch($event);

        return $event;
    }
}
