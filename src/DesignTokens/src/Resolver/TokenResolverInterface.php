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

use Symfony\UX\DesignTokens\Exception\LogicException;

/**
 * Resolves the application's token sources for a set of DTCG Resolver inputs.
 *
 * This is the resolver of the DTCG Resolver Module: inputs select modifier
 * contexts, the selected sources are merged in resolution order, and aliases
 * are resolved on the merged tree.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface TokenResolverInterface
{
    /**
     * @param array<string, string|int|float> $inputs
     *
     * @throws LogicException when inputs are given but no Resolver document is configured
     */
    public function resolve(array $inputs): TokenResolution;

    /**
     * Every combination of inputs the configured Resolver document can produce.
     *
     * Empty without a Resolver document: a plain list of token files has
     * exactly one resolution.
     *
     * @return list<array<string, string|int|float>>
     */
    public function getPermutations(): array;

    /**
     * Every modifier of the configured Resolver document, with its contexts
     * in authored order and its default context, or null when it has none.
     *
     * Empty without a Resolver document.
     *
     * @return array<string, array{contexts: list<string>, default: string|null}>
     */
    public function getModifiers(): array;
}
