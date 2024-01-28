<?php

namespace Symfony\UX\TwigComponent\Twig\Processor;

interface ProcessorInterface
{
    public function supports(string $source): bool;

    public function process(string $source): string;
}
