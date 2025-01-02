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

use Closure;
use Twig\Loader\LoaderInterface;
use Twig\Source;

/**
 * A Twig loader that loads templates from a callback. Made for testing.
 *
 * The callback will be called twice for each template: once to check if the
 * template exists and once to get the template content.
 */
class TwigCallbackLoader implements LoaderInterface
{
    /**
     * @param Closure $callback A callback that returns the template content
     */
    public function __construct(
        private readonly Closure $callback
    ) {
    }
    
    public function getSourceContext(string $name): Source
    {
        return new Source(($this->callback)($name), $name);
    }

    public function getCacheKey(string $name): string
    {
        return '';
    }

    public function isFresh(string $name, int $time): bool
    {
        return true;
    }

    /**
     * The callback will be called with the template name and should return
     * false if the template does not exist.
     */
    public function exists(string $name): bool
    {
        return false !== ($this->callback)($name);
    }
}
