<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Attribute;

/**
 * An attribute to register a listener for component events.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS |\Attribute::IS_REPEATABLE)]
final class AsComponentListener
{
    public function __construct(
        public string $component,
        public string $event,
        public ?string $method = null,
        public int $priority = 0,
    )  {
    }
}
