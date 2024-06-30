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
    private const FAVORITE_SETS = [
        'ri',
        'flowbite',
        'tabler',
        'bi',
        'lucide',
        'iconoir',
        'bx',
        'octoicons',
        'iconoir',
        'bootstrap',
        'heroicons',
        'phosphor',
        'ph',
    ];

    private array $iconSets;

    private array $terms = [
        'crypto',
        'bitcoin',
    ];

    public function __construct(
        private Iconify $iconify,
    ) {
    }

    /**
     * @return array<IconSet>
     */
    public function findAll(?int $limit = null, ?int $offset = null): array
    {
        if (!isset($this->iconSets)) {
            $iconSets = [];
            foreach ($this->iconify->collections() as $identifier => $data) {
                $iconSets[$identifier] = self::createIconSet($identifier, $data);
                // Filter out some icon sets
                foreach ($this->terms as $term) {
                    if (str_contains($identifier, $term)) {
                        break;
                    }
                }
            }
            $this->iconSets = $iconSets;
        }

        return \array_slice($this->iconSets, $offset ?? 0, $limit);
    }

    public function findAllByCategory(string $category, ?int $limit = null): array
    {
        $iconSets = $this->findAll();
        // foreach ($this->terms as $term) {
        //     $iconSets = array_filter($iconSets, fn(IconSet $iconSet) => !str_contains(strtolower($iconSet->getName()), $term));
        // }
        $iconSets = array_filter($iconSets, fn (IconSet $iconSet) => str_contains(strtolower($iconSet->getCategory()), rtrim($category, 's')));

        $score = match ($category) {
            'flag' => fn (IconSet $set) => [str_contains($set->getPrefix(), 'flag') ? 1 : 0, $set->getTotal()],
            default => fn (IconSet $set) => [$set->getTotal()],
        };

        usort($iconSets, fn (IconSet $a, IconSet $b) => $score($b) <=> $score($a));

        foreach ($iconSets as $iconSet) {
            dump($iconSet->getName(), $iconSet->getTotal(), $score($iconSet));
        }

        if (null === $limit) {
            return $iconSets;
        }

        return array_slice($iconSets, 0, $limit);
    }

    public function load(string $identifier): IconSet
    {
        return self::createIconSet($identifier, $this->iconify->collection($identifier));
    }

    public function get(string $identifier): IconSet
    {
        return $this->find($identifier) ?? throw new \InvalidArgumentException(\sprintf('Unknown icon set "%s"', $identifier));
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
            \in_array($identifier, self::FAVORITE_SETS, true),
        );
    }
}
