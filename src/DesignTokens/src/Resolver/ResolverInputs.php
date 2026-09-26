<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Resolver;

use Symfony\UX\DesignTokens\Exception\ResolverException;

/**
 * Merges and identifies Resolver inputs.
 *
 * Modifier names and context names are case-insensitive (Resolver Module), so
 * "Scheme: DARK" and "scheme: dark" select the same resolution and must share
 * one cache entry.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class ResolverInputs
{
    /**
     * Apply overrides on top of defaults, matching names case-insensitively.
     *
     * @param array<string, string|int|float> $defaults
     * @param array<string, string|int|float> $overrides
     *
     * @return array<string, string|int|float>
     *
     * @throws ResolverException when the overrides name one modifier twice
     */
    public static function merge(array $defaults, array $overrides): array
    {
        $seen = [];
        foreach ($overrides as $name => $value) {
            $name = (string) $name;
            $normalized = strtolower($name);
            if (isset($seen[$normalized])) {
                throw new ResolverException([\sprintf('Modifier input "%s" is provided more than once with different casing.', $name)]);
            }
            $seen[$normalized] = true;

            foreach (array_keys($defaults) as $default) {
                if (0 === strcasecmp((string) $default, $name)) {
                    unset($defaults[$default]);
                }
            }
            $defaults[$name] = $value;
        }

        return $defaults;
    }

    /**
     * A key identifying the resolution these inputs select.
     *
     * Input order and casing do not change it. Values are serialized rather
     * than JSON-encoded, so a value that is not valid UTF-8 still gets a key
     * and reaches the Resolver, which reports it as an invalid context.
     *
     * @param array<array-key, mixed> $inputs
     */
    public static function key(array $inputs): string
    {
        $canonical = [];
        foreach ($inputs as $name => $value) {
            if (\is_int($value) || \is_float($value)) {
                $value = (string) $value;
            }
            $canonical[strtolower((string) $name)] = \is_string($value) ? strtolower($value) : $value;
        }
        ksort($canonical);

        return hash('xxh128', serialize($canonical));
    }
}
