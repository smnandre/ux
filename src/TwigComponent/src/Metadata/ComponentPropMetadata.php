<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Metadata;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class ComponentPropMetadata
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $method,
    )
    {
    }
}
