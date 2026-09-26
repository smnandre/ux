<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Token;

/**
 * Contract implemented by resolved design tokens.
 *
 * A token is an immutable value object built by {@see TokenFactory} once
 * `TokenTreeBuilder` has followed every alias and reference and checked the
 * value against its DTCG type. Each of the 13 DTCG types has its own class, so
 * the type is known statically as well as through `getType()`.
 *
 * Two representations coexist and neither replaces the other. `getValue()`
 * returns the DTCG value after resolution, structured and unit-aware, which is
 * what code inspects or converts; a gradient's stop positions are clamped to
 * [0, 1] on the way. `(string) $token` produces the CSS
 * representation, which is what templates and generators emit:
 *
 *     $token->getValue();  // ['colorSpace' => 'srgb', 'components' => [0.2, 0.4, 1]]
 *     (string) $token;     // color(srgb 0.2 0.4 1)
 *
 * Applications consume tokens and do not implement this interface: only the
 * types the resolver knows about can come out of a DTCG document.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface TokenInterface extends \Stringable
{
    /** W3C DTCG token type identifier (e.g. 'color', 'dimension'). */
    public function getType(): string;

    /**
     * The DTCG value, with every alias and reference already resolved.
     *
     * Scalars for primitives, keyed arrays for composites and for the types
     * DTCG defines as objects, such as color and dimension.
     */
    public function getValue(): mixed;

    /** Optional human-readable description from the token definition. */
    public function getDescription(): ?string;

    /**
     * Whether the token is deprecated, with or without a migration message.
     *
     * Taken from the closest group that declares `$deprecated` when the token
     * itself does not, which a token undeprecates with `"$deprecated": false`.
     */
    public function isDeprecated(): bool;

    /**
     * The migration message, when `$deprecated` carries one instead of true.
     *
     * Null covers both a token that is not deprecated and one deprecated
     * without guidance, so read it alongside {@see isDeprecated()}.
     */
    public function getDeprecationMessage(): ?string;

    /**
     * Vendor data from the token's own `$extensions`, passed through as is.
     *
     * Unlike the type and the deprecation status, extensions are not inherited
     * from the enclosing groups.
     *
     * @return array<string, mixed>
     */
    public function getExtensions(): array;
}
