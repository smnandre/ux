<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons;

use Symfony\UX\Icons\Registry\IconSetRegistry;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconRenderer
{
    public function __construct(
        private readonly IconSetRegistry $iconSets,
        private readonly IconRegistryInterface $icons,
        private readonly array $defaultIconAttributes = [],
    ) {
    }

    private function getIconSet(string $name): ?IconSet
    {
        foreach ($this->iconSets as $prefix => $iconSet) {
            if (str_starts_with($name, $prefix.':')) {
                return $iconSet;
            }
        }

        return null;
    }

    /**
     * Renders an icon.
     *
     * Provided attributes are merged with the default attributes.
     * Existing icon attributes are then merged with those new attributes.
     *
     * Precedence order:
     *   Icon file < Renderer configuration < Renderer invocation
     *
     * @param array<string,string|bool> $attributes
     */
    public function renderIcon(string $name, array $attributes = []): string
    {
        dump($name);
        $iconSet = $this->getIconSet($name);
        dump($iconSet);

        $iconSetAttributes = $iconSet?->getIconAttributes() ?? $this->defaultIconAttributes;

        $icon = $this->icons->get($name)
            ->withAttributes($iconSetAttributes)
            ->withAttributes($attributes);

        dd($icon);

        foreach ($this->getPreRenderers() as $preRenderer) {
            $icon = $preRenderer($icon);
        }

        return $icon->toHtml();
    }

    /**
     * @return iterable<callable(Icon): Icon>
     */
    private function getPreRenderers(): iterable
    {
        yield self::setAriaHidden(...);
    }

    /**
     * Set `aria-hidden=true` if not defined & no textual alternative provided.
     */
    private static function setAriaHidden(Icon $icon): Icon
    {
        if ([] === array_intersect(['aria-hidden', 'aria-label', 'aria-labelledby', 'title'], array_keys($icon->getAttributes()))) {
            return $icon->withAttributes(['aria-hidden' => 'true']);
        }

        return $icon;
    }
}
