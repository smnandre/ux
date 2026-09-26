<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Generator;

/**
 * Transforms a resolved token tree into a specific output format.
 *
 * Implementing this interface adds a format to `ux:design-tokens:export`. The bundle
 * autoconfigures the `ux_design_tokens.generator` tag, so registering the
 * service is enough. Give the tag a `format` attribute to name the format on
 * the command line; without one it is the service id.
 *
 *     services:
 *         App\DesignTokens\ScssGenerator:
 *             tags:
 *                 - { name: ux_design_tokens.generator, format: scss }
 *
 * A generator receives tokens that are already resolved and validated, and
 * never reads the authoring files again. Which resolution it receives is the
 * caller's decision, so the same generator serves the default selection, an
 * explicit `--input`, and every Resolver permutation.
 *
 * @author Simon André <smn.andre@gmail.com>
 */
interface GeneratorInterface
{
    /** Context key: the title of the page a format produces, as a string. */
    public const TITLE = 'title';

    /** Context key: the prefix of generated CSS custom properties, as a string. */
    public const CSS_PREFIX = 'css_prefix';

    /**
     * Context key: the tree resolved for the dark scheme, or null when the
     * application has no light and dark contexts.
     */
    public const DARK_TOKENS = 'dark_tokens';

    /**
     * Render the whole document for this format.
     *
     * The tree mirrors the token paths: every level is a group, and every leaf
     * is a `TokenInterface`. Use `TokenTree::flatten()` to walk it as
     * dot-notation paths. A token exposes its native DTCG value through
     * `$token->getValue()` and its CSS representation through `(string) $token`.
     *
     * Types the format cannot express are skipped rather than approximated, so
     * that an export never claims a fidelity it does not have.
     *
     * The context carries export options, keyed by the constants of this
     * interface. A format reads the keys it understands and ignores the others.
     *
     * @param array<array-key, mixed> $resolvedTokens nested token tree from `TokenTreeBuilder`
     * @param array<string, mixed>    $context
     *
     * @return string the complete file contents, including any trailing newline
     */
    public function generate(array $resolvedTokens, array $context = []): string;
}
