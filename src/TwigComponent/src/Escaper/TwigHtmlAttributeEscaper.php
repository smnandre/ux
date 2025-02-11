<?php

namespace Symfony\UX\TwigComponent\Escaper;

use Symfony\UX\TwigComponent\Exception\RuntimeException;
use Twig\Runtime\EscaperRuntime;

/**
 * HTML attribute escaper using Twig's EscaperRuntime.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class TwigHtmlAttributeEscaper implements HtmlAttributeEscaperInterface
{
    public function __construct(
        private readonly EscaperRuntime $escaper,
    ) {
    }

    public function escapeName(string $name, string $charset = 'UTF-8'): string
    {
        if (ctype_alpha($name)) {
            return $name;
        }

        try {
            return $this->escaper->escape($name, 'html', $charset);
        } catch (\Throwable $e) {
            throw new RuntimeException(\sprintf('An error occurred while escaping the attribute name "%s".', $name), 0, $e);
        }
    }

    public function escapeValue(string $value, string $charset = 'UTF-8'): string
    {
        if (ctype_alnum($value)) {
            return $value;
        }
        
        try {
            return $this->escaper->escape($value, 'html', $charset);
        } catch (\Throwable $e) {
            throw new RuntimeException(\sprintf('An error occurred while escaping the attribute value "%s".', $value), 0, $e);
        }
    }
}
