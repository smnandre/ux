<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\DependencyInjection\Loader\Configurator;

use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\UX\TwigComponent\Command\TwigComponentDebugCommand;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentRenderer;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\ComponentStack;
use Symfony\UX\TwigComponent\ComponentTemplateFinder;
use Symfony\UX\TwigComponent\DataCollector\TwigComponentDataCollector;
use Symfony\UX\TwigComponent\DependencyInjection\Compiler\TwigComponentPass;
use Symfony\UX\TwigComponent\EventListener\TwigComponentLoggerListener;

use Symfony\UX\TwigComponent\Twig\ComponentExtension;
use Symfony\UX\TwigComponent\Twig\ComponentLexer;
use Symfony\UX\TwigComponent\Twig\TwigEnvironmentConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $container->services()

        ->set('ux.twig_component.component_template_finder', ComponentTemplateFinder::class)
        ->args([
            service('twig.loader'),
            abstract_arg(sprintf('Added in %s.', TwigComponentPass::class)),
        ])

         ->set('ux.twig_component.component_factory', ComponentFactory::class)
            ->args([
                service('ux.twig_component.component_template_finder'),
                abstract_arg(sprintf('Added in %s.', TwigComponentPass::class)),
                service('property_accessor'),
                service('event_dispatcher'),
                abstract_arg(sprintf('Added in %s.', TwigComponentPass::class)),
            ])

        ->set('ux.twig_component.component_stack', ComponentStack::class)

        ->set('ux.twig_component.component_renderer', ComponentRenderer::class)
            ->args([
                service('twig'),
                service('event_dispatcher'),
                service('ux.twig_component.component_factory'),
                service('property_accessor'),
                service('ux.twig_component.component_stack'),
            ])
        ->setAlias(ComponentRendererInterface::class, 'ux.twig_component.component_renderer')

        ->set('.ux.twig_component.twig.lexer', ComponentLexer::class)

        ->set('.ux.twig_component.twig.environment_configurator', TwigEnvironmentConfigurator::class)
            ->decorate('twig.configurator.environment')
            ->args([service('.ux.twig_component.twig.environment_configurator.inner')])

        ->set('ux.twig_component.twig.component_extension', ComponentExtension::class)
            ->tag('twig.extension')
            ->tag('container.service_subscriber', ['key' => ComponentRenderer::class, 'id' => 'ux.twig_component.component_renderer'])

        ->set('ux.twig_component.component_logger_listener', TwigComponentLoggerListener::class)
            ->args([
                service('debug.stopwatch')->ignoreOnInvalid(),
            ])
            ->tag('kernel.event_subscriber')

        ->set('ux.twig_component.data_collector', TwigComponentDataCollector::class)
            ->args([
                service('ux.twig_component.component_logger_listener'),
                service('twig'),
            ])
            ->tag('data_collector', [
                'template' => '@TwigComponent/Collector/twig_component.html.twig',
                'id' => 'twig_component',
                'priority' => 256,
            ])

        ->set('ux.twig_component.command.debug', TwigComponentDebugCommand::class)
            ->args([
                param('twig.default_path'),
                service('ux.twig_component.component_factory'),
                service('twig'),
                class_exists(AbstractArgument::class) ? new AbstractArgument(sprintf('Added in %s.', TwigComponentPass::class)) : [],
                param('anonymous_template_directory'),
            ])
            ->tag('console.command')

        ->alias('console.command.stimulus_component_debug', 'ux.twig_component.command.debug')
            ->deprecate('symfony/ux-twig-component', '2.13', '%alias_id%')
    ;
};
