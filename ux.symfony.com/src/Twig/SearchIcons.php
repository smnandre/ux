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

    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
    ) {
    }

    public function getIcons(): array
    {
        if (!$this->query) {
            return [];
        }

        dump($this->set);

        $icons = $this->http
            ->request('GET', 'https://api.iconify.design/search', [
                'query' => array_filter([
                    'query' => $this->query,
                    'limit' => 32,
                    'prefix' => $this->set,
                ]),
            ])
            ->toArray()['icons']
        ;

        return array_map(
            fn (string $icon) => sprintf('https://api.iconify.design/%s.svg', str_replace(':', '/', $icon)),
            array_combine($icons, $icons)
        );
    }

    public function getSets(): array
    {
        return $this->cache->get('iconify_sets', function () {
            return $this->http->request('GET', 'https://api.iconify.design/collections')
                ->toArray()
            ;
        });
    }
}
