<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveListener;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Scroll')]
class Scroll
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    #[LiveProp(writable: true, onUpdated: 'onQueryUpdated')]
    public string $query = '';

    #[LiveProp]
    public int $page = 1;

    #[LiveProp]
    public bool $auto = false;

    #[LiveAction]
    public function more()
    {
        ++$this->page;
    }

    public function onQueryUpdated($previousValue): void
    {
        $this->emitSelf('QueryUpdated', ['query' => $this->query]);
    }

    #[LiveListener('QueryUpdated')]
    public function reset(): void
    {
        $this->page = 1;
    }

    public function hasMore(): bool
    {
        return $this->page < \count($this->loadItems());
    }

    public function getItems(): array
    {
        $item = $this->loadItems()[$this->page - 1];

        $items = [];
        for ($i = 0; $i < 10; ++$i) {
            $items[] = [
                'id' => $this->page * 10 + $i,
                'emoji' => $item,
                'text' => $this->query ?? '-',
            ];
        }

        return $items;
    }

    private function loadItems()
    {
        return [
            '🏜️',
            '🏔️',
            '⛺',
            '🏝️',
            '🛶',
            '🏞️',
            '🎡',
            '🏖️',
            '🌋',
            '🏕️',
        ];
    }
}
