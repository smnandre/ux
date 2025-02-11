<?php

namespace Symfony\UX\TwigComponent\Escaper;

interface HtmlAttributeEscaperInterface
{
    /**
     * Validate and sanitize an attribute name.
     */
    public function escapeName(string $name, string $charset = 'UTF-8'): string;

    /**
     * Escape an attribute value following HTML attribute encoding rules.
     */
    public function escapeValue(string $value, string $charset = 'UTF-8'): string;
}
