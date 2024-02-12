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
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Icon:IconSetCard')]
class IconSetCard
{
    public IconSet $iconSet;

    public ?int $index = null;

    public ?int $samples = 10;

    public function __construct(private Iconify $iconify)
    {
    }

    public function getSampleIcons(): array
    {
        $sampleIcons = [
            ['home', 'house'],
            ['user', 'person', 'profile'],
            ['settings', 'cog', 'gear'],
            ['search', 'magnifying-glass'],
            ['arrow-down', 'arrow-bottom', 'arrow-to-bottom'],
            ['love', 'heart', 'heart-check'],
            ['star', 'star-empty'],
            ['sun', 'sun-light'],
            ['grid', 'layout-grid','view-grid', 'grid-four', 'grid-on'],
            ['image', 'photo', 'media-image'],
            ['edit', 'pencil', 'note-pencil'],
            ['trash', 'bin', 'delete-bin', 'trash-can', 'trash-bin'],
            ['map', 'map-trifold'],
            ['cart', 'shopping-cart'],
            ['check-circle', 'checkmark-circle', 'circle-check', 'checkbox-circle'],
            //['warning', 'warning-triangle', 'alert-triangle'],


        ];

        $icons = [];
        $collectionIcons = array_flip($this->iconify->collectionIcons($this->iconSet->getIdentifier()));

        $suffixes = $this->iconify->collection($this->iconSet->getIdentifier())['suffixes'] ?? [];
        $prefixes = $this->iconify->collection($this->iconSet->getIdentifier())['prefixes'] ?? [];

        foreach ($sampleIcons as $sampleVariants) {
            foreach ($sampleVariants as $icon) {
                if (isset($collectionIcons[$icon])) {
                    $icons[] = $icon;
                    break;
                }

                foreach ($suffixes as $suffix => $label) {
                    $icon = $icon.'-'.$suffix;
                    if (isset($collectionIcons[$icon])) {
                        $icons[] = $icon;
                        break;
                    }
                }

                foreach ($prefixes as $prefix => $label) {
                    $icon = $prefix.'-'.$icon;
                    if (isset($collectionIcons[$icon])) {
                        $icons[] = $icon;
                        break;
                    }
                }
            }
        }

        return array_slice($icons, 0, $this->samples);
    }


}
