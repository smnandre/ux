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
final class HtmlSyntaxParser
{
    public function parse(array $tokens): string
    {
        $output = '';
        $stack = [];

        foreach ($tokens as $token) {
            switch ($token['type']) {
                case 'TWIG_VERBATIM':
                case 'TWIG_COMMENT':
                case 'TWIG_BLOCK':
                case 'TWIG_PRINT':
                    $output .= $token['value'];
                    break;
                case 'TWIG_TAG_SELF_CLOSING':
                    $output .= $this->transformSelfClosingComponent($token['value']);
                    break;
                case 'TWIG_TAG_OPEN':
                    $output .= $this->transformOpenComponent($token['value']);
                    $stack[] = $this->extractComponentName($token['value']);
                    break;
                case 'TWIG_TAG_CLOSE':
                    if ($stack) {
                        $openName = array_pop($stack);
                        $output .= $this->transformCloseComponent($openName, $token['value']);
                    } else {
                        $output .= $token['value'];
                    }
                    break;
                case 'TEXT':
                default:
                    $output .= $token['value'];
            }
        }
        if (!empty($stack)) {
            throw new \RuntimeException("Unclosed component tag: <twig:{$stack[count($stack)-1]}>");
        }

        return $output;
    }

    private function extractComponentName(string $tag): string
    {
        if (preg_match('#<\s*/?\s*twig:([A-Za-z0-9_\-:\.@]+)#', $tag, $m)) {
            return $m[1];
        }

        return 'unknown';
    }

    private function transformOpenComponent(string $tag): string
    {
        $name = $this->extractComponentName($tag);

        if ($name === 'block') {
            if (preg_match('/name\s*=\s*["\']([^"\']+)["\']/', $tag, $mm)) {
                $blockName = $mm[1];
                return "{% block {$blockName} %}";
            }

            return '{% block content %}';
        }

        $attrs = $this->extractAttributes($tag);

        return $this->attributesToTwigWithClause($name, $attrs);
    }

    private function transformCloseComponent(string $openName, string $closeTag): string
    {
        $closeName = $this->extractComponentName($closeTag);
        if ($openName !== $closeName) {
            throw new \RuntimeException("Mismatched closing tag: expected </twig:{$openName}> but found </twig:{$closeName}>");
        }
        if ($openName === 'block') {
            return '{% endblock %}';
        }

        return '{% endcomponent %}';
    }

    private function extractAttributes(string $tag): array
    {
        $attrs = [];

        $rest = preg_replace('#^<\s*/?\s*twig:[A-Za-z0-9_\-:\.@]+\s*#', '', trim($tag));
        $rest = preg_replace('#/?>\s*$#', '', $rest);

        $pattern = '/
        (\{\{\s*\.\.\.\s*([^\}\s]+)\s*\}\}) |                    # spread
        ([A-Za-z0-9_\-:\.@]+)\s*=\s*"((?:\\\\.|[^"\\\\])*)" |    # double quotes
        ([A-Za-z0-9_\-:\.@]+)\s*=\s*\'((?:\\\\.|[^\'\\\\])*)\'|  # single quotes
        ([A-Za-z0-9_\-:\.@]+)\s*=\s*\{\{\s*(.+?)\s*\}\} |        # curly braces
        \b([A-Za-z0-9_\-:\.@]+)\b                                # truthy
        /xs';

        preg_match_all($pattern, $rest, $matches, PREG_SET_ORDER);

        foreach ($matches as $m) {
            if (!empty($m[1])) { // spread
                $attrs[] = ['type' => 'spread', 'value' => $m[2]];
            } elseif (!empty($m[3]) && isset($m[4])) { // double quotes
                $key = $m[3];
                $val = $m[4];
                if (strpos($key, ':') === 0) {
                    $name = substr($key, 1);
                    // Accept only plain variable/expression (no mustache, no quotes)
                    if (
                        preg_match('/^\s*[a-zA-Z_][a-zA-Z0-9_\.]*(\(.*\))?\s*$/', $val) ||
                        preg_match('/^\s*[a-zA-Z_][a-zA-Z0-9_\.]*(\s*[\+\-\*\/]\s*[a-zA-Z0-9_\.]+)*\s*$/', $val)
                    ) {
                        $attrs[] = ['type' => 'dynamic', 'name' => $name, 'value' => trim($val)];
                    } else {
                        $attrs[] = ['type' => 'static', 'name' => $name, 'value' => $val];
                    }
                } else {
                    $this->classifyAttribute($attrs, $key, $val);
                }
            } elseif (!empty($m[5]) && isset($m[6])) { // single quotes
                $key = $m[5];
                $val = $m[6];
                if (strpos($key, ':') === 0) {
                    $name = substr($key, 1);
                    // Accept only plain variable/expression (no mustache, no quotes)
                    if (
                        preg_match('/^\s*[a-zA-Z_][a-zA-Z0-9_\.]*(\(.*\))?\s*$/', $val) ||
                        preg_match('/^\s*[a-zA-Z_][a-zA-Z0-9_\.]*(\s*[\+\-\*\/]\s*[a-zA-Z0-9_\.]+)*\s*$/', $val)
                    ) {
                        $attrs[] = ['type' => 'dynamic', 'name' => $name, 'value' => trim($val)];
                    } else {
                        $attrs[] = ['type' => 'static', 'name' => $name, 'value' => $val];
                    }
                } else {
                    $this->classifyAttribute($attrs, $key, $val);
                }
            } elseif (!empty($m[7]) && isset($m[8])) { // curly braces
                $this->classifyAttribute($attrs, $m[7], '{{ '.$m[8].' }}');
            } elseif (!empty($m[9])) { // truthy
                $attrs[] = ['type' => 'truthy', 'name' => $m[9]];
            }
        }

        return $attrs;
    }

    private function classifyAttribute(array &$attrs, $key, $val): void
    {
        $count = preg_match_all('/\{\{[\s\S]*?\}\}/', $val, $dummy);
        $val_trimmed = trim(preg_replace('/\{\{[\s\S]*?\}\}/', '', $val));

        // Only a single mustache and nothing else
        if ($count === 1 && $val_trimmed === '') {
            // remove mustache, use as dynamic
            $inner = trim($dummy[0][0], '{} ');
            $attrs[] = ['type' => 'dynamic', 'name' => $key, 'value' => $inner];
        }
        // Multiple mustaches or mustache with text
        else if ($count > 0) {
            $attrs[] = ['type' => 'static', 'name' => $key, 'value' => $val];
        } else {
            $attrs[] = ['type' => 'static', 'name' => $key, 'value' => $val];
        }
    }

    private function quoteKeyIfNeeded($key)
    {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $key)) {
            return $key;
        }

        return "'".str_replace("'", "\\'", $key)."'";
    }

    private function attributesToTwig(string $name, array $attrs): string
    {
        if (empty($attrs)) return "{{ component('{$name}') }}";
        $parts = [];
        foreach ($attrs as $attr) {
            if ($attr['type'] === 'spread') {
                $parts[] = "...{$attr['value']}";
            } elseif ($attr['type'] === 'dynamic') {
                $key = $this->quoteKeyIfNeeded($attr['name']);
                $val = $attr['value'];
                $val = str_replace(['\\n', "\\'"], ["\n", "'"], $val);
                $parts[] = "{$key}: ({$val})";
            } elseif ($attr['type'] === 'static') {
                $key = $this->quoteKeyIfNeeded($attr['name']);
                $parts[] = "{$key}: ".$this->toTwigConcat($attr['value']);
            } elseif ($attr['type'] === 'truthy') {
                $key = $this->quoteKeyIfNeeded($attr['name']);
                $parts[] = "{$key}: true";
            }
        }

        return "{{ component('{$name}', { ".implode(', ', $parts).' }) }}';
    }

    private function attributesToTwigWithClause(string $name, array $attrs): string
    {
        if (empty($attrs)) return "{% component '{$name}' %}";
        $parts = [];
        foreach ($attrs as $attr) {
            if ($attr['type'] === 'spread') {
                $parts[] = "...{$attr['value']}";
            } elseif ($attr['type'] === 'dynamic') {
                $key = $this->quoteKeyIfNeeded($attr['name']);
                $val = $attr['value'];
                $val = str_replace(['\\n', "\\'"], ["\n", "'"], $val);
                $parts[] = "{$key}: ({$val})";
            } elseif ($attr['type'] === 'static') {
                $key = $this->quoteKeyIfNeeded($attr['name']);
                $parts[] = "{$key}: ".$this->toTwigConcat($attr['value']);
            } elseif ($attr['type'] === 'truthy') {
                $key = $this->quoteKeyIfNeeded($attr['name']);
                $parts[] = "{$key}: true";
            }
        }

        return "{% component '{$name}' with { ".implode(', ', $parts).' } %}';
    }

    private function transformSelfClosingComponent(string $tag): string
    {
        $name = $this->extractComponentName($tag);
        $attrs = $this->extractAttributes($tag);

        return $this->attributesToTwig($name, $attrs);
    }

    private function toTwigConcat(string $value): string
    {
        if ($value === '') {
            return "''";
        }

        $value = str_replace('\\"', '"', $value);

        // Split on {{ ... }} blocks, preserving them
        $chunks = preg_split('/(\{\{\s*.*?\s*\}\})/s', $value, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
        $parts = [];
        foreach ($chunks as $chunk) {
            if (preg_match('/^\{\{\s*(.*?)\s*\}\}$/s', $chunk, $m)) {
                $parts[] = '('.$m[1].')';
            } else {
                $chunk = str_replace(["'", "\n", "\r"], ["\\'", "\\n", ''], $chunk);
                if ($chunk === '') continue;
                $parts[] = "'$chunk'";
            }
        }
        if (count($parts) === 0) {
            return "''";
        }
        if (count($parts) === 1) {
            return $parts[0];
        }

        return implode('~', $parts);
    }
}
