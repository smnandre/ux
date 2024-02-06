<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\Icon;

use App\Iconify;
use App\Model\Icon\Icon;
use App\Model\Icon\IconSet;

class IconRepository
{
    public function __construct(
        private Iconify $iconify,
    )
    {
    }

    public function get(string $identifier): Icon
    {
        $iconify = $this->iconify->icon($identifier);
    }

    private static function getIconsByIconSet(string $identifier): array
    {
        return new IconSet(
            $identifier,
            $data['name'],
            $data['author'],
            $data['license'],
            $data['total'] ?? null,
            $data['version'] ?? null,
            $data['samples'] ?? [],
            $data['height'] ?? null,
            $data['displayHeight'] ?? null,
            $data['category'] ?? null,
            $data['tags'] ?? [],
            $data['palette'] ?? null,
        );
    }
}
