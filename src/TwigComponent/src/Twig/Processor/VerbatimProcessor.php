<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Twig\Processor;

/**
 * Extract all verbatim blocks from the source code.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class VerbatimProcessor implements ProcessorInterface
{
    public function supports(string $source): bool
    {
        return str_contains($source, 'verbatim');
    }

    public function process(string $source): string
    {
        // REMOVE ALL VERBATIM BLOCKS FROM THE SOURCE CODE
        // NOTE: This is a very simple implementation that does not support nested verbatim blocks.

        $pattern = '/{%\s*verbatim\s*%}.*?{%\s*endverbatim\s*%}/s';

        return preg_replace($pattern, ' ', $source);
    }
}
