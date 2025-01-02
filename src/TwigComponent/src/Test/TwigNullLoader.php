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

use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * A Twig loader that always returns empty for a template.
 */
class TwigNullLoader implements LoaderInterface
{
    private LoaderInterface $loader;
    
    public function __construct()
    {
        $this->loader = new TwigCallbackLoader(fn() => '');
    }
    
    public function getSourceContext(string $name): Source
    {
        return $this->loader->getSourceContext($name);
    }
    
    public function getCacheKey(string $name): string
    {
        return $this->loader->getCacheKey($name);
    }
    
    public function isFresh(string $name, int $time): bool
    {
        return $this->loader->isFresh($name, $time);
    }
    
    public function exists(string $name): bool
    {
        return $this->loader->exists($name);
    }
}
