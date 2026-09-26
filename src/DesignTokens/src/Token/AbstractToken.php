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
 * Shared constructor for all token types.
 *
 * @template TValue
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
abstract class AbstractToken implements TokenInterface
{
    /**
     * @var TValue
     */
    protected readonly mixed $value;

    /** @var array<string, mixed> */
    protected readonly array $extensions;

    protected readonly ?string $description;

    protected readonly bool|string|null $deprecated;

    /**
     * @param array<string, mixed> $extensions
     */
    public function __construct(
        mixed $value,
        ?string $description = null,
        array $extensions = [],
        bool|string|null $deprecated = null,
    ) {
        $this->value = $value;
        $this->description = $description;
        $this->extensions = $extensions;
        $this->deprecated = $deprecated;
    }

    /**
     * @return TValue
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isDeprecated(): bool
    {
        // A message deprecates just as much as true does; only false and an
        // absent $deprecated leave the token current.
        return false !== $this->deprecated && null !== $this->deprecated;
    }

    public function getDeprecationMessage(): ?string
    {
        return \is_string($this->deprecated) && '' !== $this->deprecated ? $this->deprecated : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Write a composite member by the rules of its own DTCG type.
     *
     * {@see TokenFactory::project()} carries the rule and the reasoning.
     */
    protected static function member(string $type, mixed $value, string $fallback = ''): string
    {
        return TokenFactory::project($type, $value, $fallback);
    }
}
