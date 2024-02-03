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

use Symfony\Ux\Icons\Svg\Icon;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconRenderer
{
    public function __construct(
        private IconRegistryInterface $registry,
        private array $defaultIconAttributes = [],
    ) {
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    public function renderIcon(string $name, array $attributes = []): string
    {
        $deferred = $attributes['defer'] ?? false;
        unset($attributes['defer']);
        [$content, $iconAttr] = $this->registry->get($name);

        if ($deferred) {
            $this->stack->push($name);

            return (new Icon('<use xlink:href="#'.self::idFor($name).'"/>'))
                ->withAttributes($attributes)
                ->toHtml();
        }

        return $this->getIcon($name)
                ->withAttributes($attributes)
                ->toHtml();
        $iconAttr = array_merge($iconAttr, $this->defaultIconAttributes);

        return sprintf(
            '<svg%s>%s</svg>',
            self::normalizeAttributes([...$iconAttr, ...$attributes]),
            $content,
        );
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    private static function normalizeAttributes(array $attributes): string
    {
        return array_reduce(
            array_keys($attributes),
            static function (string $carry, string $key) use ($attributes) {
                $value = $attributes[$key];

                return match ($value) {
                    true => "{$carry} {$key}",
                    false => $carry,
                    default => sprintf('%s %s="%s"', $carry, $key, $value),
                };
            },
            ''
        );
    }
}
