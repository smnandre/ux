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
 * Remove all comments from the source code.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class CommentProcessor implements ProcessorInterface
{
    public function supports(string $source): bool
    {
        return str_contains($source, '{#');
    }

    public function process(string $source): string
    {
        $pattern = '/\{#\s*.*?\s*#\}/s';

        return preg_replace($pattern, ' ', $source);
    }
}
