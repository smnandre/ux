<?php

namespace App;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class Iconify
{
    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
    ) {
    }

    public function search(string $query, ?string $prefix = null, int $limit = 32): array
    {
        return $this->http
            ->request('GET', 'https://api.iconify.design/search', [
                'query' => array_filter([
                    'query' => $query,
                    'limit' => $limit,
                    'prefix' => $prefix,
                ]),
            ])
            ->toArray()
        ;
    }

    public function collection(string $name): ?array
    {
        return $this->collections()[$name] ?? null;
    }

    public function collections(): array
    {
        return $this->cache->get('iconify-collections', function () {
            return $this->http->request('GET', 'https://api.iconify.design/collections')
                ->toArray()
            ;
        });
    }
}
