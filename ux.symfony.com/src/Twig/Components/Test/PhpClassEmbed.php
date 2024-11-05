<?php

namespace App\Twig\Components\Test;

use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent('Test:ClassEmbed')]
class PhpClassEmbed
{
    public int $value;
}
