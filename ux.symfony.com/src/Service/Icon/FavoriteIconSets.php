<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Icon;

use Countable;

/**
 * Provides a list of our favorite IconSets.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @implements \IteratorAggregate<string>
 *
 * @internal
 */
final class FavoriteIconSets implements \IteratorAggregate, Countable
{
    private const FAVORITE_SETS = [
        'ri',
        'tabler',
        'bi',
        'lucide',
        'iconoir',
        'bx',
        'octoicons',
        'iconoir',
        'bootstrap',
    ];

    /**
     * @param string[] $names
     */
    public function __construct(
        private readonly array $names = [],
    )
    {
    }

    public function has(string $name): bool
    {
        return in_array($name, $this->names, true);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->names);
    }

    public function count(): int
    {
        return \count($this->names);
    }
}
