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

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class IconRenderer
{
    public function __construct(
        private IconRegistryInterface $registry,
        private IconStack $stack,
        private array $defaultIconAttributes = [],
        private array $defaultDeferredAttributes = [],
    ) {
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    public function renderIcon(string $name, array $attributes = []): string
    {
        $deferred = $attributes['defer'] ?? false;

        unset($attributes['defer']);

        if ($deferred) {
            $this->stack->push($name);

            return sprintf('<svg%s><use xlink:href="#%s"/></svg>', self::normalizeAttributes($attributes), self::idFor($name));
        }

        [$content, $iconAttr] = $this->getIcon($name);

        return sprintf(
            '<svg%s>%s</svg>',
            self::normalizeAttributes([...$iconAttr, ...$attributes]),
            $content,
        );
    }

    /**
     * @param array<string,string|bool> $attributes
     */
    public function renderDeferred(array $attributes = []): string
    {
        if (!$this->stack->count()) {
            return '';
        }

        $return = sprintf('<svg%s>', self::normalizeAttributes([...$this->defaultDeferredAttributes, ...$attributes]));

        foreach ($this->stack as $name) {
            [$content, $iconAttr] = $this->getIcon($name);
            $iconAttr['id'] = self::idFor($name);

            $return .= sprintf('<symbol%s>%s</symbol>', self::normalizeAttributes($iconAttr), $content);
        }

        return $return.'</svg>';
    }

    private function getIcon(string $name): array
    {
        [$content, $iconAttr] = $this->registry->get($name);

        $iconAttr = array_merge($iconAttr, $this->defaultIconAttributes);

        return [$content, $iconAttr];
    }

    private static function idFor(string $name): string
    {
        return 'ux-icon-'.str_replace(['/', ':'], ['-', '--'], $name);
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
