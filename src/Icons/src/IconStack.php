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

use Symfony\Contracts\Service\ResetInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconStack implements ResetInterface, \IteratorAggregate, \Countable
{
    /** @var array<string,true> */
    private array $icons = [];

    public function push(string $name): void
    {
        if (isset($this->icons[$name])) {
            return;
        }

        $this->icons[$name] = true;
    }

    public function getIterator(): \Traversable
    {
        try {
            return new \ArrayIterator(array_keys($this->icons));
        } finally {
            $this->reset();
        }
    }

    public function count(): int
    {
        return \count($this->icons);
    }

    public function reset(): void
    {
        $this->icons = [];
    }
}
