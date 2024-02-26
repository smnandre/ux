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

use App\Service\Icon\IconSetRepository;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Icon:IconSetGrid')]
class IconSetGrid
{
    public ?string $category = null;

    public ?int $limit = 12;

    public ?int $samples = 12;

    public function __construct(
        private readonly IconSetRepository $iconSetRepository,
    )
    {
    }

    public function getIconSets(): array
    {
        if (null !== $this->category) {
            return $this->iconSetRepository->findAllByCategory($this->category, $this->limit);
        }

        return $this->iconSetRepository->findAll($this->limit);
    }
}
