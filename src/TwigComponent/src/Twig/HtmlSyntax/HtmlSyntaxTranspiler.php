<?php

namespace Symfony\UX\TwigComponent\Twig\HtmlSyntax;

/**
 * Transforms HTML-like component syntax into standard Twig syntax.
 *
 * Takes input with <twig:component> tags and converts it to {% component ... %}
 * or {{ component(...) }} syntax.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class HtmlSyntaxTranspiler
{
    public function __construct(
        private HtmlSyntaxParser $parser = new HtmlSyntaxParser(),
        private HtmlSyntaxTokenizer $tokenizer = new HtmlSyntaxTokenizer(),
    ) {
    }

    public function transpile(string $input): string
    {
        $tokens = $this->tokenizer->tokenize($input);

        return $this->parser->parse($tokens);
    }
}
