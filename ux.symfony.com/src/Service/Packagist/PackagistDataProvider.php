<?php

namespace App\Service\Packagist;

use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PackagistDataProvider
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private CacheInterface $cache,
    )
    {
    }

    public function getPackageData(string $packageName): array
    {
        return $this->cache->get('package-data-'.str_replace('/', '--',  $packageName), function (ItemInterface $item) use ($packageName) {
            $item->expiresAfter(604800); // 1 week

            return $this->fetchPackageData($packageName);
        });
    }

    private function fetchPackageData(string $packageName): array
    {
        $response = $this->httpClient->request('GET', 'https://packagist.org/packages/'.$packageName.'.json');

        $packageData = $response->toArray();

        return $packageData['package'] ?? [];
    }
}
