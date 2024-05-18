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

use Traversable;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class ComponentPropsMetadata implements \IteratorAggregate, \Countable
{
    private array $props = [];

    /**
     * @return void
     */
    public function add(
        string $name,
        ?string $method,
    ): void
    {
        $this->props[$name] = new ComponentPropMetadata($name, $method);
    }

    /**
     * @return array<string, ComponentPropMetadata>
     */
    public function all(): array
    {
        return $this->props;
    }
    public function get(string $name): ?ComponentPropMetadata
    {
        return $this->props[$name] ?? null;
    }

    /**
     * @return Traversable<string, ComponentPropMetadata>
     */
    public function getIterator(): Traversable
    {
        return new \ArrayIterator($this->props);
    }

    public function count(): int
    {
        return \count($this->props);
    }
}
