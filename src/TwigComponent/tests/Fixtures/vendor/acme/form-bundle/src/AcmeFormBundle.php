<?php

namespace Acme\FormBundle;

use Acme\FormBundle\Twig\Components\Bar;
use Acme\FormBundle\Twig\Components\BarFoo;
use Acme\FormBundle\Twig\Components\Foo;
use Acme\FormBundle\Twig\Components\FooBar;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class AcmeFormBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $containerConfigurator, ContainerBuilder $containerBuilder): void
    {
        $containerConfigurator->services()

            ->set('acme_form.twig_component.bar')
                ->class(Bar::class)
                ->tag('twig.component')

            ->set('acme_form.twig_component.bar_foo')
                ->class(BarFoo::class)
                ->tag('twig.component')

            ->set('acme_form.twig_component.foo')
                ->class(Foo::class)
                ->tag('twig.component')

            ->set('acme_form.twig_component.foo_bar')
                ->class(FooBar::class)
                ->tag('twig.component')
        ;
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
