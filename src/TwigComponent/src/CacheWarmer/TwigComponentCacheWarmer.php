<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\CacheWarmer;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Component\HttpKernel\CacheWarmer\CacheWarmerInterface;
use Symfony\Contracts\Service\ServiceSubscriberInterface;
use Symfony\UX\TwigComponent\ComponentProperties;
use Symfony\UX\TwigComponent\TemplateProperties;
use Symfony\UX\TwigComponent\Twig\NodeVisitor\PropsCollector;
use Twig\Environment;

/**
 * Warm the TwigComponent metadata caches.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class TwigComponentCacheWarmer implements CacheWarmerInterface, ServiceSubscriberInterface
{
    /**
     * As this cache warmer is optional, dependencies should be lazy-loaded, that's why a container should be injected.
     */
    public function __construct(
        private readonly ContainerInterface $container,
    ) {
    }

    public static function getSubscribedServices(): array
    {
        return [
            'ux.twig_component.component_properties' => ComponentProperties::class,
            'ux.twig_component.template_properties' => TemplateProperties::class,
            'twig' => '?'.Environment::class,
        ];
    }

    public function warmUp(string $cacheDir, ?string $buildDir = null): array
    {
        // Twig Template Props
        $this->twig ??= $this->container->get('twig');

        /** @var PropsCollector $collector */
        $collector = $this->twig->getExtension('Symfony\UX\TwigComponent\Twig\ComponentExtension')->getCollector();

        if (!is_dir($directory = \sprintf('%s%s%s', $cacheDir, \DIRECTORY_SEPARATOR, 'ux'))) {
            mkdir($directory, 0777, true);
        }
        $cacheFile = \sprintf('%s%s%s', $directory, \DIRECTORY_SEPARATOR, 'twig_component.props.php');
        PhpArrayAdapter::create($cacheFile, new NullAdapter())->warmUp(['props' => $collector->getProps()]);

        // PHP Class Props
        /** @var ComponentProperties $properties */
        $properties = $this->container->get('ux.twig_component.component_properties');
        $properties->warmup();

        // Twig Template Props
        /** @var TemplateProperties $properties */
        $properties = $this->container->get('ux.twig_component.template_properties');
        $properties->setCollector($collector);
        $properties->warmup();

        return [];
    }

    public function isOptional(): bool
    {
        return true;
    }
}
