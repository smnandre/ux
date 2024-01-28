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
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class HtmlTag
{
    public function __construct(
        public readonly string $html,
        public readonly string $name,
        public readonly int $startPosition,
        public readonly int $endPosition,
        public readonly bool $isOpening,
        public readonly bool $isClosing,
    )
    {
    }
}
