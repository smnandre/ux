<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Twig\Components\Icon;

use App\Service\Icon\Iconify;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('Icon:IconSearch')]
class IconSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public ?string $query = null;

    #[LiveProp(writable: true, url: true)]
    public ?string $set = null;

    #[LiveProp(writable: true)]
    public ?string $style = null;

    #[LiveProp]
    public bool $hideSelect = false;

    public function __construct(private Iconify $iconify)
    {
    }

    public function icons(): array
    {
        if (!$this->query) {
            if (!$this->set) {
               return [];
            }
        }

        if (!$this->query && $this->set) {
            $icons = array_slice($this->iconify->collectionIcons($this->set), 0, 256);

            $result = [];
            foreach ($icons as $icon) {
                $icon = $this->set.':'.$icon;
                $result[$icon] = sprintf('https://api.iconify.design/%s.svg', $icon);
            }
            return $result;
        }

        $icons = $this->iconify->search($this->query, $this->set, 256)['icons'];

        return array_map(
            fn (string $icon) => sprintf('https://api.iconify.design/%s.svg', str_replace(':', '/', $icon)),
            array_combine($icons, $icons)
        );
    }

    public function collections(): array
    {
        return $this->iconify->collections();
    }

    public function getCategories(): array
    {
        if (!$this->set) {
            return [];
        }

        return $this->iconify->collectionCategories($this->set);
    }

    public function getStyles(): array
    {
        if (!$this->set) {
            return [];
        }


        $styles = [];
        foreach($this->iconify->collectionStyles($this->set) as $type => $fixes) {
            $styles = $styles + $fixes;
        }

        return $styles;
    }
}
