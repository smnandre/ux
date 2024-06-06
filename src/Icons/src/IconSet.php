<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class IconSet
{
    /**
     * @param array<string, string|bool> $iconAttributes
     */
    public function __construct(
        private string $prefix,
        private string $iconDir,
        private array $iconAttributes = [],
    ) {
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function getIconDir(): string
    {
        return $this->iconDir;
    }

    /**
     * @return array<string, string|bool>
     */
    public function getIconAttributes(): array
    {
        return $this->iconAttributes;
    }
}
