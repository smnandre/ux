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

use App\Iconify;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
class SearchIcons
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public ?string $query = null;

    #[LiveProp(writable: true)]
    public ?string $set = null;

    #[LiveProp]
    public bool $hideSelect = false;

    public function __construct(private Iconify $iconify)
    {
    }

    public function icons(): array
    {
        if (!$this->query) {
            return [];
        }

        $icons = $this->iconify->search($this->query, $this->set)['icons'];

        return array_map(
            fn (string $icon) => sprintf('https://api.iconify.design/%s.svg', str_replace(':', '/', $icon)),
            array_combine($icons, $icons)
        );
    }

    public function collections(): array
    {
        return $this->iconify->collections();
    }
}
