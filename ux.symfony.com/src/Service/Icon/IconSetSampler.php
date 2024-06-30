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

use App\Model\Icon\IconSet;

final class IconSetSampler
{
    /**
     * @var array<string, array<string>> iconSetIdentifier => icon names
     */
    private array $samples = [];

    public function __construct(private readonly Iconify $iconify)
    {
    }

    public function getSampleIcons(IconSet $iconSet, int $nbSamples = 15): array
    {
        $samples = $this->samples[$iconSet->getIdentifier()] ?? null;

        if (null === $samples) {
            if ($iconSet->isBrandsSocial()) {
                $samples = $this->findBrandSamples($iconSet);
            } elseif ($iconSet->isEmoji()) {
                $samples = $this->findEmojiSamples($iconSet);
            } elseif ($iconSet->isMapsFlags()) {
                $samples = $this->findFlagSamples($iconSet);
            } else {
                $samples = $this->findSamples($iconSet);
            }
            $this->samples[$iconSet->getIdentifier()] = $samples;
        }

        return array_slice($samples, 0, $nbSamples);
    }

    private function findEmojiSamples(IconSet $iconSet): array
    {
        $sampleIcons = [
            ['😄', 'smile'],
            ['😍', 'heart-eyes'],
            ['❤️', 'red-heart'],
            ['😎', 'sunglasses'],
            ['😂', 'tears-of-joy'],

            ['👍', 'thumbs-up'],
            ['👏', 'clap'],

            ['🌍', 'earth'],
            ['🔥', 'fire'],
            ['⭐', 'star'],
            ['🌒', 'waxing_crescent_moon','waxing-crescent-moon', 'crescent_moon', 'moon'],

            ['🌳', 'tree', 'deciduous-tree', 'deciduous_tree'],
            ['🍄', 'mushroom'],

            ['🍑', 'peach',],
            ['🍕', 'pizza'],

            ['🐼', 'panda', 'panda-face'],
            ['🦁', 'lion', 'lion-face'],
            ['🐙', 'octopuss'],

            ['⚽','football', 'soccer', 'ball', 'soccer-ball'],
            ['🚀', 'rocket'],
            ['🛸', 'ufo'],
            ['🎁', 'gift'],
        ];

        return $this->lookupIcons($iconSet, $sampleIcons);
    }

    private function findBrandSamples(IconSet $iconSet): array
    {
        $sampleIcons = [
            ['apple'],
            ['android'],
            ['adobe'],
            ['google'],
            ['firefox'],
            ['chrome'],
            ['github'],
            ['twitter'],
            ['linkedin'],
            ['slack'],
            ['discord'],
            ['telegram'],
            ['instagram'],
            ['youtube'],
            ['tiktok'],
            ['pinterest'],
            ['evernote'],
            ['dribbble'],
            ['behance'],
            ['medium'],
            ['snapchat'],
            ['tripadvisor'],
            ['whatsapp'],
            ['twitch'],
            ['phpstorm'],
            ['docker'],
            ['php'],
            ['html', 'html5'],
            ['css', 'css3'],
            ['javascript', 'js'],
            ['bootstrap'],
            ['tailwind'],
        ];

        return $this->lookupIcons($iconSet, $sampleIcons);
    }

    private function findFlagSamples(IconSet $iconSet): array
    {
        $sampleIcons = [
            ['🇦🇺', 'au', 'aus', 'australia'],
            ['🇧🇷', 'br', 'bra', 'brazil'],
            ['🇨🇦', 'ca', 'can', 'canada'],
            ['🇩🇪', 'de', 'deu', 'germany'], // 'de' is the ISO 639-1 language code for German
            ['🇪🇸', 'es', 'esp', 'spain'],
            ['🇫🇷', 'fr', 'fra', 'france'],
            ['🇬🇧', 'gb', 'gbr', 'uk', 'united-kingdom'],
            ['🇮🇹', 'it', 'ita', 'italy'],
            ['🇯🇵', 'jp', 'jpn', 'japan'],
            ['🇺🇸', 'us', 'usa', 'united-states'],
        ];

        return $this->lookupIcons($iconSet, $sampleIcons);
    }

    private function findSamples(IconSet $iconSet): array
    {
        $sampleIcons = [
            ['home', 'house'],
            ['user', 'person', 'profile'],
            ['settings', 'cog', 'gear'],
            ['search', 'magnifying-glass'],
            ['arrow-down', 'arrow-bottom', 'arrow-to-bottom', 'down-two'],

            ['love', 'heart', 'heart-check'],
            ['star', 'star-empty'],
            ['sun', 'sun-light'],
            ['grid', 'layout-grid', 'view-grid', 'grid-four', 'grid-on'],
            ['image', 'photo', 'media-image'],

            ['edit', 'pencil', 'note-pencil'],
            ['trash', 'bin', 'delete-bin', 'trash-can', 'trash-bin'],
            ['map', 'map-trifold'],
            ['cart', 'shopping-cart'],
            ['check-circle', 'checkmark-circle', 'circle-check', 'checkbox-circle'],
        ];

        return $this->lookupIcons($iconSet, $sampleIcons);
    }

    private function lookupIcons(IconSet $iconSet, array $sampleIcons): array
    {
        $collectionIcons = array_flip($this->iconify->collectionIcons($iconSet->getIdentifier()));
        $collection = $this->iconify->collection($iconSet->getIdentifier());

        $icons = [];
        foreach ($sampleIcons as $i => $sampleVariants) {
            foreach ($sampleVariants as $icon) {
                $iconNames = [$icon];
                foreach ($collection['suffixes'] ?? [] as $suffix => $label) {
                    $iconNames[] = $icon.'-'.$suffix;
                }
                foreach ($collection['prefixes'] ?? [] as $prefix => $label) {
                    $iconNames[] = $prefix.'-'.$icon;
                }

                foreach ($iconNames as $iconName) {
                    if (isset($collectionIcons[$iconName])) {
                        $icons[$i] ??= $iconName;
                        break;
                    }
                }
            }
        }

        return $icons;
    }

}
