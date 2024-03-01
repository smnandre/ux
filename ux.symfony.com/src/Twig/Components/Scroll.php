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
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Scroll')]
class Scroll
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $query;

    #[LiveProp]
    public int $page = 1;

    #[LiveProp]
    public bool $auto = false;

    #[LiveAction]
    public function more()
    {
        $this->page++;
    }

    public function hasMore(): bool
    {
        return $this->page < count($this->loadItems());
    }

    public function getItems(): array
    {
        $item = $this->loadItems()[$this->page - 1];

        $items = [];
        for ($i = 0; $i < 10; $i++) {
            $items[] = [
                'id' => $this->page * 10 + $i,
                'emoji' => $item,
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
