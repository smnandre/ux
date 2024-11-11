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

use Symfony\Component\PropertyAccess\Exception\ExceptionInterface as PropertyAccessExceptionInterface;
use Symfony\UX\LiveComponent\EventListener\DeferLiveComponentSubscriber;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\Event\PostMountEvent;
use Symfony\UX\TwigComponent\Event\PreMountEvent;
use Symfony\UX\TwigComponent\Event\PreRenderEvent;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
class LiveComponentFactory
{
    public function __construct(
        private ComponentFactory $inner,
    ) {
    }
    
    protected function onPreMount(PreMountEvent $event)
    {
        // model binding parser
        
        // DataModelLivePropsSubscriber
        // data-model="live"
        // $event->setData($data);
    }

    /**
     * @param PostMountEvent $event
     * @return void
     *             
     * @see DeferLiveComponentSubscriber
     */
    protected function onPostMountLazy(PostMountEvent $event)
    {
        // extraData(loading, loading-tag)...
        // $event->setData($data);
    }

    /**
     * @param PostMountEvent $event
     * @return void
     *             
     * @see QueryStringInitializeSubscriber
     */
    protected function onPostMountQueryString(PostMountEvent $event)
    {
        // AddLiveAttributes
        // $event->setTemplate($template);
        // $event->setVariables($variables);
        //
        //
        //   $queryStringData = $this->queryStringPropsExtractor->extract($request, $metadata, $event->getComponent());
        // 
        //         $component = $event->getComponent();
        // 
        //         foreach ($queryStringData as $name => $value) {
        //             try {
        //                 $this->propertyAccessor->setValue($component, $name, $value);
        //             } catch (PropertyAccessExceptionInterface $exception) {
        //                 // Ignore errors
        //             }
        //         }    }
    }
}
