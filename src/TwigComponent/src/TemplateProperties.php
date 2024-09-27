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

use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\UX\TwigComponent\Twig\NodeVisitor\PropsCollector;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class TemplateProperties
{
    private const CACHE_KEY = 'ux.twig_component.template_properties';

    private array $templateMetadata;

    private PropsCollector $propsCollector;

    public function __construct(
        ?array $templateMetadata = [],
        private readonly ?AdapterInterface $cache = null,
    ) {
        $cacheItem = $this->cache?->getItem(self::CACHE_KEY);

        $this->templateMetadata = $cacheItem?->isHit() ? [...$templateMetadata, ...$cacheItem->get()] : $templateMetadata;
    }

    public function setCollector(PropsCollector $propsCollector): void
    {
        $this->propsCollector = $propsCollector;
    }

    /**
     * @return array<string, mixed>
     */
    public function getProperties(string $template): array
    {
        return $this->templateMetadata[$template] ??= $this->collectTemplateMetadata($template);
    }

    private function collectTemplateMetadata(string $template): array
    {
        if (isset($this->propsCollector)) {
            return $this->propsCollector->getProps()[$template] ?? [];
        }

        return [];
    }

    public function warmup(): void
    {
        if (!$this->cache) {
            throw new \LogicException('The cache must be set before warming up the cache.');
        }

        if (!isset($this->propsCollector)) {
            throw new \LogicException('The collector must be set before warming up the cache.');
        }

        foreach ($this->propsCollector->getProps() as $template => $props) {
            $this->templateMetadata[$template] ??= $props;
        }

        $this->cache->save($this->cache->getItem(self::CACHE_KEY)->set($this->templateMetadata));
    }
}
