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

use App\Service\UxPackageRepository;
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

    public function __construct(private HttpClientInterface $http)
    {
    }

    public function getIcons(): array
    {
        if (!$this->query) {
            return [];
        }

        $icons = $this->http->request('GET', sprintf('https://api.iconify.design/search?query=%s&limit=32', $this->query))
            ->toArray()['icons']
        ;

        return array_map(
            fn (string $icon) => sprintf('https://api.iconify.design/%s.svg', str_replace(':', '/', $icon)),
            array_combine($icons, $icons)
        );
    }
}
