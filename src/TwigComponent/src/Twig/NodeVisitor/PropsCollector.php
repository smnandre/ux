<?php

namespace Symfony\UX\TwigComponent\Twig\NodeVisitor;

final class PropsCollector
{
    private array $props = [];

    public function collect(string $template, array $props): void
    {
        $this->props[$template] = $props;
    }

    public function getProps(): array
    {
        return $this->props;
    }
}
