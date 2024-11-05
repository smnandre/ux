<?php

namespace App\Twig\Components\Test;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Test:Class')]
class PhpClass
{
    public int $value;
}
