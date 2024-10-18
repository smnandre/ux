<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Changelog;

use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * @internal
 */
final class ChangelogProvider
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly CacheInterface $cache,
    ) {
    }

    public function getChangelog(int $page = 1): array
    {
        $changelog = [];

        foreach ($this->getReleases('ux', $page) as $release) {
            $changelog[] = $release;
        }

        return $changelog;
    }

    public function getPackageChangelog(string $repo,  int $page = 1): array
    {
        $changelogMd = $this->cache->get('changelog-symfony-'.$repo.'-'.$page, function (CacheItemInterface $item) use ($repo, $page) {
            $item->expiresAfter(604800); // 1 week

            return $this->fetchPackageChangelog('symfony', $repo, $page);
        });

        $changelog = [];
        $changelogMarkdown = explode("\n## ", $changelogMd);

        return $changelogMarkdown;
    }

    private function getReleases(string $repo, int $page = 1): array
    {
        return $this->cache->get('releases-symfony-'.$repo.'-'.$page, function (CacheItemInterface $item) use ($repo, $page) {
            $item->expiresAfter(604800); // 1 week

            return $this->fetchReleases('symfony', $repo, $page);
        });
    }

     /**
     * @return string
     *
     * @internal
     */
    private function fetchPackageChangelog(string $owner, string $repo): string
    {
        // https://github.com/symfony/ux-twig-component/blob/2.x/CHANGELOG.md
        // https://raw.githubusercontent.com/symfony/ux-twig-component/2.x/CHANGELOG.md
        $response = $this->httpClient->request('GET', sprintf('https://raw.githubusercontent.com/%s/%s/2.x/CHANGELOG.md', $owner, $repo));

        return $response->getContent();
    }

    /**
     * @return array<int, array{id: int, name: string, version: string, date: string, body: string}>
     *
     * @internal
     */
    private function fetchReleases(string $owner, string $repo, int $page = 1, int $perPage = 20): array
    {
        $response = $this->httpClient->request('GET', \sprintf('https://api.github.com/repos/%s/%s/releases', $owner, $repo), [
            'query' => [
                'page' => $page,
                'per_page' => $perPage,
            ],
        ]);

        $releases = [];
        foreach ($response->toArray() as $release) {
            $releases[$release['id']] = [
                'id' => $release['id'],
                'name' => $release['name'],
                'version' => $release['tag_name'],
                'date' => $release['published_at'],
                'body' => $release['body'],
            ];
        }

        return $releases;
    }
}
