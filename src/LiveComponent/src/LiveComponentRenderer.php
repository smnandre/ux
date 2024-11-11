<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\LiveComponent;

use Symfony\UX\LiveComponent\EventListener\ResetDeterministicIdSubscriber;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\Event\PostRenderEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
class LiveComponentRenderer implements ComponentRendererInterface
{
    public function __construct(
        private ComponentRendererInterface $inner,
    ) {
    }

    public function createAndRender(string $name, array $props = []): string
    {
        $this->inner->createAndRender($name, $props);
    }

    protected function onPreRender(PreRenderEvent $event): ?string
    {
        // AddLiveAttributes
        // $event->setTemplate($template);
        // $event->setVariables($variables);
    }

    /**
     * @see DeferLiveComponentSubscriber
     */
    protected function onPreRenderLoading(PreRenderEvent $event)
    {
        // change template
        // $event->setTemplate($template);

        //  $variables['loadingTemplate'] = self::DEFAULT_LOADING_TEMPLATE;
        // $variables['loadingTag'] = self::DEFAULT_LOADING_TAG;
        // $variables['loading'] = $mountedComponent->getExtraMetadata('loading');
        // $variables['componentTemplate'] = $componentTemplate;
        //
        // $event->setVariables($variables);
    }

    /**
     * @param PostRenderEvent $event
     * @return void
     *
     * @see ResetDeterministicIdSubscriber
     */
    protected function postRender(PostRenderEvent $event)
    {
        // $event->setHtml($html);
        // if (!$this->componentStack->getCurrentComponent()) {
        //     $this->idCalculator->reset();
        // }
    }
}
