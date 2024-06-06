<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Registry;

use IteratorAggregate;
use Symfony\UX\Icons\IconSet;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @implements IteratorAggregate<string, IconSet>
 * @internal
 */
final class IconSetRegistry implements IteratorAggregate
{
    /**
     * @param array<string, IconSet> $iconSets
     */
    private array $iconSets = [];

    /**
     * @param array<string, array{string, array<string, mixed>} $iconSets
     */
    public function __construct(array $iconSets = [])
    {
        foreach ($iconSets as $name => $data) {
            $path = $data['path'];
            unset($data['path']);
            $this->addIconSet($name, $path, $data);
        }
    }

    public function get(string $name): IconSet
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('Icon set "%s" not found.', $name));
        }

        return $this->iconSets[$name];
    }

    public function has(string $name): bool
    {
        return array_key_exists($name, $this->iconSets);
    }

    /**
     * @param array<string, mixed> $configuration
     */
    public function addIconSet(string $name, string $directory, array $configuration = []): void
    {
        $this->iconSets[$name] = new IconSet($name, $directory, $configuration);
    }

    public function getIterator(): \ArrayIterator
    {
        $iconSets = $this->iconSets;
        uksort($iconSets, fn($a, $b) => strlen($b) <=> strlen($a));

        return new \ArrayIterator($this->iconSets);
    }
}
