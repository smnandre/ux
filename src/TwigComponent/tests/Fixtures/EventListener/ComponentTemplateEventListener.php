<?php

namespace Symfony\UX\TwigComponent\Tests\Fixtures\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

class ComponentTemplateEventListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [PreRenderEvent::class => 'onPreRender'];
    }

    public function onPreRender(PreRenderEvent $event): void
    {
        if (str_contains($event->getMetadata()->getName(), 'XYZ')) {
            $event->setTemplate(str_replace('.html.twig', '.xyz.html.twig', $event->getTemplate()));
        }
    }
}
