<?php

namespace Symfony\UX\TwigComponent\Component;

final class Component 
{
    public function __construct(
        private readonly string $name,
        private readonly array $props = [],
    ) {
    }

    public function getName(): string
    {
    }
    
    public function getProps(): array
    {
        return $this->props;
    }
    
    public function withProps(array $props): self
    {
        return new self($this->name, $props);
    }
}
