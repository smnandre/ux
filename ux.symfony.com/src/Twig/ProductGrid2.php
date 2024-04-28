<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig;

use App\Service\EmojiCollection;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\Attribute\PostHydrate;
use Symfony\UX\LiveComponent\ComponentToolsTrait;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\ExposeInTemplate;
use Symfony\UX\TwigComponent\Attribute\PostMount;

#[AsLiveComponent('ProductGrid2')]
class ProductGrid2
{
    use ComponentToolsTrait;
    use DefaultActionTrait;

    private const PER_PAGE = 12;

    #[LiveProp(writable: true, onUpdated: 'refreshHash')]
    public int $hue = 40;

    #[LiveProp]
    public int $page = 1;

    private bool $reset = false;

    public function __construct(private readonly EmojiCollection $emojis)
    {
    }

    public function refreshHash(): void
    {
        $this->reset = true;
    }

    #[PostHydrate]
    public function postHydrate(): void
    {
        if ($this->reset) {
            $this->page = 1;
        }
    }

    #[LiveAction]
    public function more(): void
    {
        ++$this->page;
    }

    public function getItems(): array
    {
        $emojis = $this->emojis->paginate($this->page, self::PER_PAGE);

        $items = [];
        foreach ($emojis as $i => $emoji) {
            $items[] = [
                'id' => ($this->page - 1) * self::PER_PAGE + $i,
                'emoji' => $emoji,
                'hue' => $this->hue,
            ];
        }

        return $items;
    }

    #[ExposeInTemplate('per_page')]
    public function getPerPage(): int
    {
        return self::PER_PAGE;
    }

    public function hasMore(): bool
    {
        return \count($this->emojis) > ($this->page * self::PER_PAGE);
    }
}
