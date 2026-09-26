<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens;

use Symfony\UX\DesignTokens\Exception\LogicException;
use Symfony\UX\DesignTokens\Exception\ResolverException;
use Symfony\UX\DesignTokens\Exception\TokenNotFoundException;
use Symfony\UX\DesignTokens\Token\TokenInterface;

/**
 * Reads the resolved design tokens of an application.
 *
 * Type against this interface to consume tokens, and decorate the service
 * aliased on it to change what an application sees. {@see TokenRegistry} is the
 * implementation the bundle wires.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface TokenRegistryInterface
{
    /**
     * Retrieve a single token by dot-notation path.
     *
     * Every lookup takes optional Resolver inputs. They select contexts for
     * this call only; the configured inputs still apply to every modifier they
     * do not name. Modifier and context names are case-insensitive.
     *
     * @param string                          $path   e.g. `'color.brand.primary'`
     * @param array<string, string|int|float> $inputs
     *
     * @throws TokenNotFoundException when the path does not resolve to a token
     * @throws ResolverException      when an input names an unknown modifier or context
     * @throws LogicException         when inputs apply and no Resolver document is configured
     */
    public function get(string $path, array $inputs = []): TokenInterface;

    /**
     * Return a token by path, or null when the path is missing or identifies a group.
     *
     * @param array<string, string|int|float> $inputs
     *
     * @throws ResolverException when an input names an unknown modifier or context
     * @throws LogicException    when inputs apply and no Resolver document is configured
     */
    public function find(string $path, array $inputs = []): ?TokenInterface;

    /**
     * Whether the path identifies a token.
     *
     * @param array<string, string|int|float> $inputs
     *
     * @throws ResolverException when an input names an unknown modifier or context
     * @throws LogicException    when inputs apply and no Resolver document is configured
     */
    public function has(string $path, array $inputs = []): bool;

    /**
     * Return all resolved tokens as a nested array.
     *
     * @param array<string, string|int|float> $inputs
     *
     * @return array<array-key, mixed>
     *
     * @throws ResolverException when an input names an unknown modifier or context
     * @throws LogicException    when inputs apply and no Resolver document is configured
     */
    public function all(array $inputs = []): array;

    /**
     * Return every resolved token under its dot-notation path.
     *
     * @param array<string, string|int|float> $inputs
     *
     * @return array<string, TokenInterface>
     *
     * @throws ResolverException when an input names an unknown modifier or context
     * @throws LogicException    when inputs apply and no Resolver document is configured
     */
    public function flatten(array $inputs = []): array;

    /**
     * Every combination of Resolver inputs the configured document can produce.
     *
     * Empty when no Resolver document is configured, since a plain list of
     * token files has exactly one resolution.
     *
     * @return list<array<string, string|int|float>>
     */
    public function getPermutations(): array;

    /**
     * Every modifier of the configured Resolver document, with its contexts
     * in authored order and its default context, or null when it has none.
     *
     * Use it to build controls that select a context; pass the chosen values
     * as the inputs of the other methods.
     *
     * @return array<string, array{contexts: list<string>, default: string|null}>
     */
    public function getModifiers(): array;
}
