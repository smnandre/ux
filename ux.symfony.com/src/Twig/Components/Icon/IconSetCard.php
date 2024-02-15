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
use App\Service\Icon\Iconify;
use App\Service\Icon\IconSetSampler;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Icon:IconSetCard')]
class IconSetCard
{
    public IconSet $iconSet;

    public ?int $index = null;

    public ?int $samples = 10;

    public function __construct(private IconSetSampler $iconSetSampler)
    {
    }

    public function sampleIcons(): array
    {
        return $this->iconSetSampler->getSampleIcons($this->iconSet, $this->samples);
    }
}
