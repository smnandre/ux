<?php

namespace App\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent('LazyFoo')]
class LazyFoo
{
    use DefaultActionTrait;

    #[LiveProp]
    public int $renders = 0;

    public function __invoke()
    {
        $this->renders++;
    }

    public function generate(): string
    {
        sleep(3);
        return sprintf('%s **\'%s"%s**', date('H:i'), date('s'), intval(gettimeofday()['usec'] / 1000));
    }
}
