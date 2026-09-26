<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Bridge\Tailwind;

use Symfony\UX\DesignTokens\Exception\InvalidArgumentException;
use Symfony\UX\DesignTokens\Generator\GeneratorInterface;
use Symfony\UX\DesignTokens\Token\ShadowToken;
use Symfony\UX\DesignTokens\Token\TokenFactory;
use Symfony\UX\DesignTokens\Token\TokenInterface;
use Symfony\UX\DesignTokens\Token\TypographyToken;

/**
 * Generates Tailwind CSS v4 theme variables from resolved DTCG tokens.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
final class ThemeGenerator implements GeneratorInterface
{
    private const PATH_NAMESPACES = [
        'aspect' => 'aspect',
        'aspects' => 'aspect',
        'blur' => 'blur',
        'blurs' => 'blur',
        'breakpoint' => 'breakpoint',
        'breakpoints' => 'breakpoint',
        'container' => 'container',
        'containers' => 'container',
        'easing' => 'ease',
        'leading' => 'leading',
        'letter-spacing' => 'tracking',
        'perspective' => 'perspective',
        'radius' => 'radius',
        'radii' => 'radius',
        'rounded' => 'radius',
        'screens' => 'breakpoint',
        'shadow' => 'shadow',
        'shadows' => 'shadow',
        'space' => 'spacing',
        'spacing' => 'spacing',
        'tab-size' => 'tab-size',
        'text' => 'text',
        'tracking' => 'tracking',
        'zoom' => 'zoom',
    ];

    /** Two consecutive path segments naming a Tailwind namespace, such as font.size. */
    private const PAIR_NAMESPACES = [
        'font.size' => 'text',
        'font.weight' => 'font-weight',
        'line.height' => 'leading',
        'letter.spacing' => 'tracking',
    ];

    /** Segments that only restate the DTCG type, skipped when naming a variable. */
    private const TYPE_SEGMENTS = ['color', 'colors', 'dimension', 'dimensions', 'number', 'numbers', 'font', 'fonts', 'size', 'sizes', 'weight', 'weights'];

    public function generate(array $resolvedTokens, array $context = []): string
    {
        $variables = [];
        $origins = [];

        foreach ($this->flatten($resolvedTokens) as $path => $token) {
            foreach ($this->project($path, $token) as $variable => $value) {
                // Two paths may name one variable with one value, such as a
                // typography's size and the size token it aliases.
                if (isset($origins[$variable]) && $origins[$variable] !== $path && $variables[$variable] !== $value) {
                    throw new InvalidArgumentException(\sprintf('Design token paths "%s" and "%s" generate the same Tailwind theme variable "%s".', $origins[$variable], $path, $variable));
                }
                if (false !== strpbrk($value, ";{}\r\n")) {
                    throw new InvalidArgumentException(\sprintf('Design token "%s" produces an unsafe Tailwind theme value.', $path));
                }

                $origins[$variable] = $path;
                $variables[$variable] = $value;
            }
        }

        $lines = ['@theme static {'];
        foreach ($variables as $variable => $value) {
            $lines[] = \sprintf('  %s: %s;', $variable, $value);
        }
        $lines[] = '}';

        return implode("\n", $lines)."\n";
    }

    /**
     * @param array<array-key, mixed> $tokens
     *
     * @return iterable<string, TokenInterface>
     */
    private function flatten(array $tokens, string $prefix = ''): iterable
    {
        foreach ($tokens as $name => $value) {
            $path = '' === $prefix ? (string) $name : $prefix.'.'.$name;
            if ($value instanceof TokenInterface) {
                yield $path => $value;
            } elseif (\is_array($value)) {
                yield from $this->flatten($value, $path);
            }
        }
    }

    /** @return array<string, string> */
    private function project(string $path, TokenInterface $token): array
    {
        $matched = $this->pathNamespace($path);
        $start = $matched[1] ?? 0;

        // Tailwind reads a text size's line height, letter spacing and weight
        // from --text-*--modifier. It has no modifier for the font family,
        // which a fontFamily token of the same name already provides.
        if ($token instanceof TypographyToken) {
            $value = $token->getValue();
            $name = $this->name($path, $start, ['typography', 'type', 'text']);

            return [
                '--text-'.$name => TokenFactory::project('dimension', $value['fontSize']),
                '--text-'.$name.'--line-height' => TokenFactory::project('number', $value['lineHeight']),
                '--text-'.$name.'--letter-spacing' => TokenFactory::project('dimension', $value['letterSpacing']),
                '--text-'.$name.'--font-weight' => TokenFactory::project('fontWeight', $value['fontWeight']),
            ];
        }

        $namespace = match (true) {
            $token instanceof ShadowToken => $this->isInsetShadow($token) ? 'inset-shadow' : 'shadow',
            'color' === $token->getType() => 'color',
            'fontFamily' === $token->getType() => 'font',
            'fontWeight' === $token->getType() => 'font-weight',
            'cubicBezier' === $token->getType() => 'ease',
            'dimension' === $token->getType() => $matched[0] ?? 'spacing',
            'number' === $token->getType() => $matched[0] ?? null,
            default => null,
        };

        if (null === $namespace) {
            return [];
        }

        $name = $this->name($path, $start, $this->prefixes($namespace));

        return ['--'.$namespace.('' === $name ? '' : '-'.$name) => (string) $token];
    }

    /**
     * The first path segment, or pair of segments, naming a Tailwind namespace.
     *
     * @return array{string, int}|null the namespace and the index of the first naming segment
     */
    private function pathNamespace(string $path): ?array
    {
        $segments = array_map(strtolower(...), explode('.', $path));
        foreach ($segments as $index => $segment) {
            $pair = $segment.'.'.($segments[$index + 1] ?? '');
            if (isset(self::PAIR_NAMESPACES[$pair])) {
                return [self::PAIR_NAMESPACES[$pair], $index + 2];
            }
            if (isset(self::PATH_NAMESPACES[$segment])) {
                return [self::PATH_NAMESPACES[$segment], $index + 1];
            }
        }

        return null;
    }

    /** @return list<string> */
    private function prefixes(string $namespace): array
    {
        $prefixes = array_keys(array_filter(
            self::PATH_NAMESPACES,
            static fn (string $mapped): bool => $mapped === $namespace,
        ));

        return [...$prefixes, ...match ($namespace) {
            'color' => ['color', 'colors'],
            'font' => ['font', 'fonts', 'font-family', 'family', 'families'],
            'font-weight' => ['font-weight', 'weight', 'weights'],
            'ease' => ['ease'],
            'inset-shadow' => ['inset-shadow', 'inset-shadows', 'elevation'],
            'shadow' => ['elevation'],
            default => [],
        }];
    }

    /**
     * The variable name: the path after the segments that named the namespace,
     * without leading segments that only restate the type.
     *
     * Empty for the $root of a namespace group, which Tailwind names after the
     * namespace alone, as in --spacing.
     *
     * @param list<string> $prefixes
     */
    private function name(string $path, int $start, array $prefixes): string
    {
        $segments = explode('.', $path);
        if ('$root' === end($segments)) {
            array_pop($segments);
            if (\count($segments) === $start) {
                return '';
            }
        }
        $parts = \array_slice($segments, $start) ?: \array_slice($segments, -1);
        $skippable = [...$prefixes, ...self::TYPE_SEGMENTS];
        while (\count($parts) > 1 && \in_array(strtolower($parts[0]), $skippable, true)) {
            array_shift($parts);
        }
        $name = preg_replace('/[^\p{L}\p{N}_-]+/u', '-', strtolower(implode('-', $parts)));

        return trim($name ?? '', '-') ?: 'token';
    }

    private function isInsetShadow(ShadowToken $token): bool
    {
        foreach ($this->shadows($token) as $shadow) {
            if (true !== ($shadow['inset'] ?? false)) {
                return false;
            }
        }

        return true;
    }

    /** @return list<array<array-key, mixed>> */
    private function shadows(ShadowToken $token): array
    {
        $values = isset($token->getValue()['color']) ? [$token->getValue()] : $token->getValue();

        return array_values(array_filter($values, \is_array(...)));
    }
}
