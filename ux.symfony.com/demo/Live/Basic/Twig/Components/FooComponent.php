<?php

namespace Demo\Live\Basic\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveAction;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsLiveComponent('Basic:Foo', template: '@Demo/Live/Basic/templates/components/Foo.html.twig')]
class FooComponent
{
    use DefaultActionTrait;

    #[LiveProp]
    public string $foo;

    #[LiveAction]
    public function bar()
    {
        $this->foo = 'bar';
    }
}
