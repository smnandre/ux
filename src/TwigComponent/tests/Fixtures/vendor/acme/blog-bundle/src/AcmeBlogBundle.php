<?php

namespace Acme\BlogBundle;

use Acme\BlogBundle\Twig\Components\Bar;
use Acme\BlogBundle\Twig\Components\BarFoo;
use Acme\BlogBundle\Twig\Components\Foo;
use Acme\BlogBundle\Twig\Components\FooBar;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AcmeBlogBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        $containerConfigurator->services()

            ->set('acme_blog.twig_component.foo')
                ->class(Foo::class)
                ->tag('twig.component')

            ->set('acme_blog.twig_component.foo_bar')
                ->class(FooBar::class)
                ->tag('twig.component')
        ;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
