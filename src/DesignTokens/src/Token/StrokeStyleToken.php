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

use Symfony\UX\DesignTokens\Exception\InvalidArgumentException;

/**
 * A DTCG stroke style keyword or structured dash definition.
 *
 * Every DTCG keyword is also a CSS `border-style` keyword, so a keyword passes
 * through. The structured form has no CSS equivalent: no property carries a
 * dash array, so it projects to `dashed`, the keyword that means the same
 * thing to a browser. The pattern itself stays in `getValue()` for a consumer
 * that can use it, such as an SVG `stroke-dasharray`.
 *
 * @extends AbstractToken<string|array<string, mixed>>
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class StrokeStyleToken extends AbstractToken
{
    /**
     * @param array<string, mixed> $extensions
     *
     * @throws InvalidArgumentException when the value is neither a keyword nor a dash definition
     */
    public function __construct(
        mixed $value,
        ?string $description = null,
        array $extensions = [],
        bool|string|null $deprecated = null,
    ) {
        if (!\is_string($value) && !\is_array($value)) {
            throw new InvalidArgumentException('A strokeStyle token value must be a keyword or a dash definition.');
        }

        parent::__construct($value, $description, $extensions, $deprecated);
    }

    public function getType(): string
    {
        return 'strokeStyle';
    }

    public function __toString(): string
    {
        return \is_string($this->value) ? $this->value : 'dashed';
    }
}
