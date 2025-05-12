<?php

namespace Symfony\UX\TwigComponent\Twig\HtmlSyntax;

/**
 * Tokenizes HTML-like component syntax into a structured format, allowing for
 * easy parsing and transpilation into standard Twig syntax.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
class HtmlSyntaxTokenizer
{
    private string $input;
    private int $length;
    private int $position = 0;

    /**
     * Tokenize the input, producing an array of [type, value, line]
     */
    public function tokenize(string $input): array
    {
        $this->input = $input;
        $this->length = strlen($input);
        $this->position = 0;
        $tokens = [];
        $line = 1;

        while ($this->position < $this->length) {
            // Bloc verbatim ou raw
            if ($block = $this->matchVerbatimOrRaw()) {
                $tokens[] = ['type' => 'TWIG_VERBATIM', 'value' => $block['content'], 'line' => $line];
                $line += substr_count($block['content'], "\n");
                continue;
            }
            // Bloc Twig (statement, print, comment)
            if ($block = $this->matchTwigBlock()) {
                $tokens[] = ['type' => $block['type'], 'value' => $block['content'], 'line' => $line];
                $line += substr_count($block['content'], "\n");
                continue;
            }
            // Tag <twig:...>
            if ($block = $this->matchTwigTag()) {
                $tokens[] = ['type' => $block['type'], 'value' => $block['content'], 'line' => $line];
                $line += substr_count($block['content'], "\n");
                continue;
            }
            // Tag </twig:...>
            if ($block = $this->matchTwigTagClose()) {
                $tokens[] = ['type' => 'TWIG_TAG_CLOSE', 'value' => $block['content'], 'line' => $line];
                $line += substr_count($block['content'], "\n");
                continue;
            }
            // Text until next bloc/twig/tag
            $start = $this->position;
            while (
                $this->position < $this->length &&
                !$this->peek('{%') &&
                !$this->peek('{{') &&
                !$this->peek('{#') &&
                !$this->peek('<twig:') &&
                !$this->peek('</twig:')
            ) {
                if ($this->input[$this->position] === "\n") $line++;
                $this->position++;
            }
            if ($this->position > $start) {
                $tokens[] = [
                    'type' => 'TEXT',
                    'value' => substr($this->input, $start, $this->position - $start),
                    'line' => $line,
                ];
            }
        }
        return $tokens;
    }

    private function matchVerbatimOrRaw(): ?array
    {
        foreach (['verbatim', 'raw'] as $type) {
            $openTag = '{% ' . $type;
            $closeTag = '{% end' . $type . ' %}';
            if ($this->peek($openTag)) {
                $start = $this->position;
                $end = strpos($this->input, $closeTag, $this->position);
                if ($end === false) {
                    // Unterminated block, read to end
                    $content = substr($this->input, $this->position);
                    $this->position = $this->length;
                } else {
                    $content = substr($this->input, $this->position, $end + strlen($closeTag) - $this->position);
                    $this->position = $end + strlen($closeTag);
                }
                return ['content' => $content];
            }
        }
        return null;
    }

    private function matchTwigBlock(): ?array
    {
        // Bloc statement {% ... %}
        if ($this->peek('{%')) {
            $start = $this->position;
            while ($this->position < $this->length && !$this->peek('%}')) {
                $this->position++;
            }
            $this->position += 2; // consume '%}'
            return [
                'type' => 'TWIG_BLOCK',
                'content' => substr($this->input, $start, $this->position - $start),
            ];
        }
        // Bloc print {{ ... }}
        if ($this->peek('{{')) {
            $start = $this->position;
            while ($this->position < $this->length && !$this->peek('}}')) {
                $this->position++;
            }
            $this->position += 2; // consume '}}'
            return [
                'type' => 'TWIG_PRINT',
                'content' => substr($this->input, $start, $this->position - $start),
            ];
        }
        // Commentaire Twig {# ... #}
        if ($this->peek('{#')) {
            $start = $this->position;
            while ($this->position < $this->length && !$this->peek('#}')) {
                $this->position++;
            }
            $this->position += 2; // consume '#}'
            return [
                'type' => 'TWIG_COMMENT',
                'content' => substr($this->input, $start, $this->position - $start),
            ];
        }
        return null;
    }

    private function matchTwigTag(): ?array
    {
        if ($this->peek('<twig:')) {
            $start = $this->position;
            // Consume until next '>'
            while ($this->position < $this->length && $this->input[$this->position] !== '>') {
                $this->position++;
            }
            if ($this->position < $this->length) {
                $this->position++; // consume '>'
            }
            $content = substr($this->input, $start, $this->position - $start);
            // Self closing?
            if (preg_match('#<twig:[^>]+/>$#', $content) || str_ends_with(trim($content), '/>')) {
                return ['type' => 'TWIG_TAG_SELF_CLOSING', 'content' => $content];
            }
            return ['type' => 'TWIG_TAG_OPEN', 'content' => $content];
        }
        return null;
    }

    private function matchTwigTagClose(): ?array
    {
        if ($this->peek('</twig:')) {
            $start = $this->position;
            while ($this->position < $this->length && $this->input[$this->position] !== '>') {
                $this->position++;
            }
            if ($this->position < $this->length) {
                $this->position++; // consume '>'
            }
            $content = substr($this->input, $start, $this->position - $start);
            return ['content' => $content];
        }
        return null;
    }

    private function peek(string $needle): bool
    {
        return substr($this->input, $this->position, strlen($needle)) === $needle;
    }
}
