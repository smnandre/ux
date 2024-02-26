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

use App\Model\Icon\IconSet;

class IconSetRepository
{
    private array $iconSets;

    private array $terms = [
        'crypto',
    ];

    public function __construct(
        private Iconify $iconify,
    )
    {
    }

    public function findAllByCategory(string $category, ?int $limit = null): array
    {
        $iconSets = $this->findAll();
        foreach ($this->terms as $term) {
            $iconSets = array_filter($iconSets, fn(IconSet $iconSet) => !str_contains(strtolower($iconSet->getName()), $term));
        }
        $iconSets = array_filter($iconSets, fn(IconSet $iconSet) => str_contains(strtolower($iconSet->getCategory()), $category));

        $score = match ($category) {
            'flag' => fn(IconSet $set) => [str_contains(strtolower($set->getName()), 'flag') ? 1 : 0, $set->getTotal()],
            default => fn(IconSet $set) => [$set->getTotal()],
        };

        usort($iconSets, fn(IconSet $a, IconSet $b) => $score($b) <=> $score($a));

        if (null === $limit) {
            return $iconSets;
        }

        return array_slice($iconSets, 0, $limit);
    }

    /**
     * @return array<IconSet>
     */
    public function findAll(?int $limit = null, ?int $offset = null): array
    {
        if (isset($this->iconSets)) {
            return array_slice($this->iconSets, 0, $limit);
        }

        $iconSets = [];
        foreach ($this->iconify->collections() as $identifier => $data) {
            // TODO not every time !! Need findAll without all data
            $collection = $this->iconify->collection($identifier);
            $data['suffixes'] = $collection['suffixes'] ?? [];
            $data['categories'] = array_keys($collection['categories'] ?? []);
            $iconSets[$identifier] = self::createIconSet($identifier, $data);
        }
        $this->iconSets = $iconSets;

        return array_slice($iconSets, $offset ?? 0, $limit);
    }

    public function load(string $identifier): IconSet
    {
        return self::createIconSet($identifier, $this->iconify->collection($identifier));
    }

    public function getPrevious(string $identifier, bool $loop = false): ?IconSet
    {
        $iconSets = $this->findAll();
        while ($iconSet = current($iconSets)) {
            if ($iconSet->getIdentifier() === $identifier) {
                return prev($iconSets) ?: ($loop ? end($iconSets) : null);
            }
            next($iconSets);
        }

        return null;
    }

    public function getFirst(): ?IconSet
    {
        $iconSets = $this->findAll();
        return reset($iconSets);
    }

    public function getLast(): ?IconSet
    {
        $iconSets = $this->findAll();
        return end($iconSets);
    }

    public function getNext(string $identifier, bool $loop = false): ?IconSet
    {
        $iconSets = $this->findAll();
        while ($iconSet = current($iconSets)) {
            if ($iconSet->getIdentifier() === $identifier) {
                return next($iconSets) ?: ($loop ? reset($iconSets) : null);
            }
            next($iconSets);
        }

        return null;
    }

    public function get(string $identifier): IconSet
    {
        return $this->find($identifier) ?? throw new \InvalidArgumentException(sprintf('Unknown icon set "%s"', $identifier));
    }

    public function find(string $identifier): ?IconSet
    {
        $iconSets = $this->findAll();
        foreach ($iconSets as $iconSet) {
            if ($iconSet->getIdentifier() === $identifier) {
                return $iconSet;
            }
        }

        return null;
    }

    private static function createIconSet(string $identifier, array $data): IconSet
    {
        return new IconSet(
            $identifier,
            $data['name'],
            $data['author'],
            $data['license'],
            $data['total'] ?? null,
            $data['version'] ?? null,
            $data['samples'] ?? [],
            $data['height'] ?? null,
            $data['displayHeight'] ?? null,
            $data['category'] ?? null,
            $data['tags'] ?? [],
            $data['palette'] ?? null,
            $data['suffixes'] ?? null,
            $data['categories'] ?? null,
        );
    }
}
