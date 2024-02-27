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

use App\Model\Icon\IconSet;
use App\Service\Icon\IconSetSampler;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Icon:IconSetCard')]
class IconSetCard
{
    public IconSet $iconSet;

    public ?int $index = null;

    public ?int $samples = 15;

    public function __construct(
        private readonly IconSetSampler $iconSetSampler,
    ) {
    }

    public function sampleIcons(): array
    {
        return $this->iconSetSampler->getSampleIcons($this->iconSet, $this->samples);
    }
}
