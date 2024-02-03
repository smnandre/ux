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

use Symfony\UX\Icons\Svg\Icon;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconRenderer
{
    public function __construct(
        private IconRegistryInterface $registry,
        private array $defaultIconAttributes = [],
    ) {
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    public function renderIcon(string $name, array $attributes = []): string
    {
        return $this->getIcon($name)
                ->withAttributes([...$this->defaultIconAttributes, ...$attributes])
                ->toHtml();
    }

    private function getIcon(string $name): Icon
    {
        return $this->registry->get($name);
    }
}
