<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Twig;

use Twig\Error\SyntaxError;
use Twig\Lexer;

/**
 * Rewrites <namespace:component> syntaxes to {% component %} syntaxes.
 *
 * A namespace resolves to a component name prefix, so that a lexer given
 * `['ux' => 'ux']` rewrites `<ux:icon />` to `{{ component('ux:icon') }}`. `twig` is
 * always recognized and resolves to the empty prefix.
 */
class TwigPreLexer
{
    /**
     * A namespace is alphanumeric and starts with a letter: `ux`, `FooBar`, `ux2`.
     *
     * No `-`, `_`, `:`, dot or non-ASCII letter, so that a namespace can never be
     * confused with a component name, an HTML tag or a Twig identifier.
     */
    private const NAMESPACE_PATTERN = '/^[A-Za-z][A-Za-z0-9]*$/';

    private string $input;
    private int $length;
    private int $position = 0;
    private int $line;

    /**
     * @var array<string, string> namespace => component name prefix, without trailing colon
     */
    private readonly array $namespaces;

    private readonly string $openingTagRegex;
    private readonly string $closingTagRegex;
    private readonly string $blockOpeningRegex;
    private readonly string $blockClosingRegex;

    /**
     * @var array<array{name: string, namespace: string, shortName: string, hasDefaultBlock: bool}>
     */
    private array $currentComponents = [];

    /**
     * @param array<string, string> $namespaces Extra namespaces, as namespace => component name
     *                                          prefix, e.g. `['ux' => 'ux']`. `twig` is always
     *                                          recognized and needs no declaring.
     *
     * @throws \InvalidArgumentException if a namespace is malformed
     *
     * @experimental the namespace argument
     */
    public function __construct(int $startingLine = 1, array $namespaces = [])
    {
        $this->line = $startingLine;

        // `<twig:` is the syntax this lexer exists for, not something to declare
        $resolved = ['twig' => ''];
        foreach ($namespaces as $namespace => $prefix) {
            if (!preg_match(self::NAMESPACE_PATTERN, $namespace = (string) $namespace)) {
                throw new \InvalidArgumentException(\sprintf('Invalid component namespace "%s": it must start with a letter and contain only letters and digits.', $namespace));
            }

            $resolved[$namespace] = rtrim((string) $prefix, ':');
        }
        $this->namespaces = $resolved;

        // longest namespace first, so that `<uxfoo:` can never be shadowed by `<ux:`
        $names = array_keys($resolved);
        usort($names, static fn (string $a, string $b) => \strlen($b) <=> \strlen($a));

        $alternation = implode('|', array_map(preg_quote(...), $names));
        $this->openingTagRegex = '/\G<('.$alternation.'):/';
        $this->closingTagRegex = '/\G<\/('.$alternation.'):/';

        // used to track block nesting while scanning raw text
        $this->blockOpeningRegex = '/\G<('.$alternation.'):block/';
        $this->blockClosingRegex = '/\G<\/('.$alternation.'):block>/';
    }

    public function preLexComponents(string $input): string
    {
        $found = false;
        foreach (array_keys($this->namespaces) as $namespace) {
            if (str_contains($input, '<'.$namespace.':')) {
                $found = true;
                break;
            }
        }

        if (!$found) {
            return $input;
        }

        $this->input = $input = str_replace(["\r\n", "\r"], "\n", $input);
        $this->length = \strlen($input);
        $output = '';

        $inTwigEmbed = false;

        while ($this->position < $this->length) {
            // ignore content inside verbatim block #947
            if ($this->consume('{% verbatim %}')) {
                $output .= '{% verbatim %}';
                $output .= $this->consumeUntil('{% endverbatim %}');
                $this->consume('{% endverbatim %}');
                $output .= '{% endverbatim %}';

                if ($this->position === $this->length) {
                    break;
                }
            }

            // ignore content inside twig comments, see #838
            if ($this->consume('{#')) {
                $output .= '{#';
                $output .= $this->consumeUntil('#}');
                $this->consume('#}');
                $output .= '#}';

                if ($this->position === $this->length) {
                    break;
                }
            }

            if ($this->consume('{% embed')) {
                $inTwigEmbed = true;
                $output .= '{% embed';
                $output .= $this->consumeUntil('%}');

                continue;
            }

            if ($this->consume('{% endembed %}')) {
                $inTwigEmbed = false;
                $output .= '{% endembed %}';

                continue;
            }

            $namespace = $this->consumeOpeningTag();
            $isTraditionalBlockOpening = false;

            if (null !== $namespace || (0 !== \count($this->currentComponents) && $isTraditionalBlockOpening = $this->consume('{% block'))) {
                $componentName = $isTraditionalBlockOpening
                    ? 'block'
                    : $this->consumeComponentName(\sprintf('Expected component name when resolving the "<%s:" syntax.', $namespace));

                // `block` is a lexer directive, not a component, under every namespace:
                // `block` is a Twig keyword, so it is never a sensible component name
                if ($isTraditionalBlockOpening || 'block' === $componentName) {
                    // if we're already inside the "default" block, let's close it
                    if (!empty($this->currentComponents) && $this->currentComponents[\count($this->currentComponents) - 1]['hasDefaultBlock'] && !$inTwigEmbed) {
                        $output .= '{% endblock %}';

                        $this->currentComponents[\count($this->currentComponents) - 1]['hasDefaultBlock'] = false;
                    }

                    if ($isTraditionalBlockOpening) {
                        // add what we've consumed so far
                        $output .= '{% block';
                        $output .= $stringUntilClosingTag = $this->consumeUntil('%}');

                        // If the last-consumed string does not match the Twig's block name regex, we assume the block is self-closing
                        $isBlockSelfClosing = '' !== preg_replace(Lexer::REGEX_NAME, '', trim($stringUntilClosingTag));

                        if ($isBlockSelfClosing && $this->consume('%}')) {
                            $output .= '%}';
                        } else {
                            $output .= $this->consumeUntilEndBlock();
                        }

                        continue;
                    }

                    $output .= $this->consumeBlock($namespace, $componentName);

                    continue;
                }

                // if we're already inside a component,
                // *and* we've just found a new component, then we should try to
                // open the default block
                if (!empty($this->currentComponents)
                    && !$this->currentComponents[\count($this->currentComponents) - 1]['hasDefaultBlock']) {
                    $output .= '{% block content %}';
                    $this->currentComponents[\count($this->currentComponents) - 1]['hasDefaultBlock'] = true;
                }

                // a null namespace only happens on the `{% block %}` path, which always
                // continues above
                \assert(null !== $namespace);

                $resolvedName = $this->resolveComponentName($namespace, $componentName);

                $attributes = $this->consumeAttributes($namespace.':'.$componentName);
                $isSelfClosing = $this->consume('/>');
                if (!$isSelfClosing) {
                    $this->consume('>');
                    $this->currentComponents[] = [
                        'name' => $resolvedName,
                        'namespace' => $namespace,
                        'shortName' => $componentName,
                        'hasDefaultBlock' => false,
                    ];
                }

                if ($isSelfClosing) {
                    // use the simpler component() format, so that the system doesn't think
                    // this is an "embedded" component with blocks
                    // see https://github.com/symfony/ux/issues/810
                    $output .= "{{ component('{$resolvedName}'".($attributes ? ", { {$attributes} }" : '').') }}';
                } else {
                    $output .= "{% component '{$resolvedName}'".($attributes ? " with { {$attributes} }" : '').' %}';
                }

                continue;
            }

            if (!empty($this->currentComponents) && null !== $closingNamespace = $this->consumeClosingTag()) {
                $closingComponentName = $this->consumeComponentName();
                $this->consume('>');

                $lastComponent = array_pop($this->currentComponents);

                // compare the literal source pair, not the resolved name, so that
                // `<admin:Card></app:Card>` fails even when both namespaces resolve alike
                if ($closingComponentName !== $lastComponent['shortName'] || $closingNamespace !== $lastComponent['namespace']) {
                    throw new SyntaxError(\sprintf("Expected closing tag '</%s:%s>' but found '</%s:%s>'.", $lastComponent['namespace'], $lastComponent['shortName'], $closingNamespace, $closingComponentName), $this->line);
                }

                // we've reached the end of this component. If we're inside the
                // default block, let's close it
                if ($lastComponent['hasDefaultBlock']) {
                    $output .= '{% endblock %}';
                }

                $output .= '{% endcomponent %}';

                continue;
            }

            $char = $this->input[$this->position];
            if ("\n" === $char) {
                ++$this->line;
            }

            // handle adding a default block if we find non-whitespace outside of a block
            if (!empty($this->currentComponents)
                && !$this->currentComponents[\count($this->currentComponents) - 1]['hasDefaultBlock']
                && preg_match('/\S/', $char)
                && !$this->check('{% block')
            ) {
                $this->currentComponents[\count($this->currentComponents) - 1]['hasDefaultBlock'] = true;
                $output .= '{% block content %}';
            }

            $output .= $char;
            $this->consumeChar();
        }

        if (!empty($this->currentComponents)) {
            $lastComponent = array_pop($this->currentComponents);
            throw new SyntaxError(\sprintf('Expected closing tag "</%s:%s>" not found.', $lastComponent['namespace'], $lastComponent['shortName']), $this->line);
        }

        return $output;
    }

    /**
     * Consumes `<namespace:` for any registered namespace and returns the namespace.
     */
    private function consumeOpeningTag(): ?string
    {
        if (!preg_match($this->openingTagRegex, $this->input, $matches, 0, $this->position)) {
            return null;
        }

        $this->position += \strlen($matches[0]);

        return $matches[1];
    }

    /**
     * Consumes `</namespace:` for any registered namespace and returns the namespace.
     */
    private function consumeClosingTag(): ?string
    {
        if (!preg_match($this->closingTagRegex, $this->input, $matches, 0, $this->position)) {
            return null;
        }

        $this->position += \strlen($matches[0]);

        return $matches[1];
    }

    private function resolveComponentName(string $namespace, string $shortName): string
    {
        $prefix = $this->namespaces[$namespace];

        return '' === $prefix ? $shortName : $prefix.':'.$shortName;
    }

    private function consumeComponentName(?string $customExceptionMessage = null): string
    {
        if (preg_match('/\G[A-Za-z0-9_:@\-.]+/', $this->input, $matches, 0, $this->position)) {
            $componentName = $matches[0];
            $this->position += \strlen($componentName);

            return $componentName;
        }

        throw new SyntaxError($customExceptionMessage ?? 'Expected component name when resolving the "<twig:" syntax.', $this->line);
    }

    /**
     * @param string $tagName The tag as written in the source, e.g. `twig:Alert` or `ea:Field`
     */
    private function consumeAttributes(string $tagName): string
    {
        $attributes = [];

        while ($this->position < $this->length && !$this->check('>') && !$this->check('/>')) {
            $this->consumeWhitespace();
            if ($this->check('>') || $this->check('/>')) {
                break;
            }

            if ($this->check('{{...') || $this->check('{{ ...')) {
                $this->consume('{{...');
                $this->consume('{{ ...');
                $attributes[] = '...'.trim($this->consumeUntil('}}'));
                $this->consume('}}');

                continue;
            }

            $isAttributeDynamic = false;

            // :someProp="dynamicVar"
            $this->consumeWhitespace();
            if ($this->check(':')) {
                $this->consume(':');
                $isAttributeDynamic = true;
            }

            $message = \sprintf('Expected attribute name when parsing the "<%s" syntax.', $tagName);
            // was called 'consumeAttributeName'
            $key = $this->consumeComponentName($message);

            // <twig:component someProp> -> someProp: true
            if (!$this->check('=')) {
                $this->consumeWhitespace();
                // don't allow "<twig:component :someProp>"
                if ($isAttributeDynamic) {
                    throw new SyntaxError(\sprintf('Expected "=" after ":%s" when parsing the "<%s" syntax.', $key, $tagName), $this->line);
                }

                $attributes[] = \sprintf('%s: true', preg_match('/[-:@]/', $key) ? "'$key'" : $key);
                $this->consumeWhitespace();
                continue;
            }

            $this->expectAndConsumeChar('=');
            $quote = $this->consumeChar(["'", '"']);

            if ($isAttributeDynamic) {
                // :someProp="dynamicVar"
                $attributeValue = $this->consumeUntil($quote);
            } else {
                $attributeValue = $this->consumeAttributeValue($quote);
            }

            $attributes[] = \sprintf('%s: %s', preg_match('/[-:@]/', $key) ? "'$key'" : $key, '' === $attributeValue ? "''" : $attributeValue);

            $this->expectAndConsumeChar($quote);
            $this->consumeWhitespace();
        }

        return implode(', ', $attributes);
    }

    /**
     * If the next character(s) exactly matches the given string, then
     * consume it (move forward) and return true.
     */
    private function consume(string $string): bool
    {
        if (str_starts_with(substr($this->input, $this->position), $string)) {
            $this->position += \strlen($string);

            return true;
        }

        return false;
    }

    private function consumeChar($validChars = null): string
    {
        if ($this->position >= $this->length) {
            throw new SyntaxError('Unexpected end of input.', $this->line);
        }

        $char = $this->input[$this->position];

        if (null !== $validChars && !\in_array($char, (array) $validChars, true)) {
            throw new SyntaxError('Expected one of [.'.implode('', (array) $validChars)."] but found '{$char}'.", $this->line);
        }

        ++$this->position;

        return $char;
    }

    /**
     * Moves the position forward until it finds $endString.
     *
     * Any string consumed *before* finding that string is returned.
     * The position is moved forward to just *before* $endString.
     */
    private function consumeUntil(string $endString): string
    {
        if (false === $endPosition = strpos($this->input, $endString, $this->position)) {
            $start = $this->position;
            $this->position = $this->length;

            return substr($this->input, $start);
        }

        $content = substr($this->input, $this->position, $endPosition - $this->position);
        $this->line += substr_count($content, "\n");
        $this->position = $endPosition;

        return $content;
    }

    private function consumeWhitespace(): void
    {
        $whitespace = substr($this->input, $this->position, strspn($this->input, " \t\n\r\0\x0B", $this->position));
        $this->line += substr_count($whitespace, "\n");
        $this->position += \strlen($whitespace);

        if ($this->check('#')) {
            $this->consume('#');
            $this->consumeUntil("\n");
            $this->consumeWhitespace();
        }
    }

    /**
     * Checks that the next character is the one given and consumes it.
     */
    private function expectAndConsumeChar(string $char): void
    {
        if (1 !== \strlen($char)) {
            throw new \InvalidArgumentException('Expected a single character.');
        }

        if ($this->position >= $this->length) {
            throw new SyntaxError("Expected '{$char}' but reached the end of the file.", $this->line);
        }

        if ($this->input[$this->position] !== $char) {
            throw new SyntaxError("Expected '{$char}' but found '{$this->input[$this->position]}'.", $this->line);
        }

        ++$this->position;
    }

    private function check(string $chars): bool
    {
        return $this->position + \strlen($chars) <= $this->length
            && 0 === substr_compare($this->input, $chars, $this->position, \strlen($chars));
    }

    private function consumeBlock(string $namespace, string $componentName): string
    {
        $attributes = $this->consumeAttributes($namespace.':'.$componentName);
        $this->consume('>');

        $blockName = '';
        foreach (explode(', ', $attributes) as $attr) {
            [$key, $value] = explode(': ', $attr);
            if ('name' === $key) {
                $blockName = trim($value, "'");
                break;
            }
        }

        if (empty($blockName)) {
            throw new SyntaxError('Expected block name.', $this->line);
        }

        $output = "{% block {$blockName} %}";

        $closingTag = \sprintf('</%s:block>', $namespace);
        if (false === strpos($this->input, $closingTag, $this->position)) {
            throw new SyntaxError("Expected closing tag '{$closingTag}' for block '{$blockName}'.", $this->line);
        }
        $blockContents = $this->consumeUntilEndBlock();

        // the sub-lexer must know the same namespaces, otherwise a `<ea:Field />`
        // nested inside a `<twig:block>` would silently pass through untouched
        $subLexer = new self($this->line, $this->namespaces);
        $output .= $subLexer->preLexComponents($blockContents);

        // scanning stops at the first `</any:block>`, so it may belong to another
        // namespace than the one that opened this block
        if (!$this->consume($closingTag) && preg_match($this->blockClosingRegex, $this->input, $matches, 0, $this->position)) {
            throw new SyntaxError(\sprintf("Expected closing tag '%s' but found '%s'.", $closingTag, $matches[0]), $this->line);
        }

        $output .= '{% endblock %}';

        return $output;
    }

    private function consumeUntilEndBlock(): string
    {
        $start = $this->position;

        $depth = 1;
        $inComment = false;
        while ($this->position < $this->length) {
            if ($inComment && '#}' === substr($this->input, $this->position, 2)) {
                $inComment = false;
            }
            if (!$inComment && '{#' === substr($this->input, $this->position, 2)) {
                $inComment = true;
            }

            // a block opened under one namespace may be closed under another one: stop at
            // the first `</any:block>` and let consumeBlock() report the mismatch
            if (!$inComment && '<' === $this->input[$this->position] && preg_match($this->blockClosingRegex, $this->input, $matches, 0, $this->position)) {
                if (1 === $depth) {
                    break;
                }

                --$depth;
            }

            if (!$inComment && '{% endblock %}' === substr($this->input, $this->position, 14)) {
                if (1 === $depth) {
                    // in this case, we want to advance ALL the way beyond the endblock
                    // strlen('{% endblock %}') = 14
                    $this->position += 14;
                    break;
                }

                --$depth;
            }

            if (!$inComment && '<' === $this->input[$this->position] && preg_match($this->blockOpeningRegex, $this->input, $matches, 0, $this->position)) {
                ++$depth;
            }

            if (!$inComment && '{% block' === substr($this->input, $this->position, 8)) {
                ++$depth;
            }

            if ("\n" === $this->input[$this->position]) {
                ++$this->line;
            }

            ++$this->position;
        }

        return substr($this->input, $start, $this->position - $start);
    }

    private function consumeAttributeValue(string $quote): string
    {
        $parts = [];
        $currentPart = '';
        while ($this->position < $this->length) {
            if ($this->check($quote)) {
                break;
            }

            if ("\n" === $this->input[$this->position]) {
                ++$this->line;
            }

            if ($this->check('{{')) {
                // mark any previous static text as complete: push into parts
                if ('' !== $currentPart) {
                    $parts[] = \sprintf("'%s'", str_replace("'", "\'", $currentPart));
                    $currentPart = '';
                }

                // consume the entire {{ }} block
                $this->consume('{{');
                $this->consumeWhitespace();
                $parts[] = \sprintf('(%s)', rtrim($this->consumeUntil('}}')));
                $this->expectAndConsumeChar('}');
                $this->expectAndConsumeChar('}');

                continue;
            }

            $currentPart .= $this->input[$this->position];
            ++$this->position;
        }

        if ('' !== $currentPart) {
            $parts[] = \sprintf("'%s'", str_replace("'", "\'", $currentPart));
        }

        return implode('~', $parts);
    }
}
