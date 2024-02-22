<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Parameter;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;
use Symfony\UX\TwigComponent\Command\TwigComponentDebugCommand;
use Symfony\UX\TwigComponent\ComponentFactory;
use Symfony\UX\TwigComponent\ComponentRenderer;
use Symfony\UX\TwigComponent\ComponentRendererInterface;
use Symfony\UX\TwigComponent\ComponentStack;
use Symfony\UX\TwigComponent\ComponentTemplateFinder;
use Symfony\UX\TwigComponent\DependencyInjection\Compiler\TwigComponentPass;
use Symfony\UX\TwigComponent\Twig\ComponentExtension;
use Symfony\UX\TwigComponent\Twig\ComponentLexer;
use Symfony\UX\TwigComponent\Twig\TwigEnvironmentConfigurator;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class TwigComponentExtension extends Extension implements ConfigurationInterface
{
    private const DEPRECATED_DEFAULT_KEY = '__deprecated__use_old_naming_behavior';

    public function load(array $configs, ContainerBuilder $container): void
    {
        if (!isset($container->getParameter('kernel.bundles')['TwigBundle'])) {
            throw new LogicException('The TwigBundle is not registered in your application. Try running "composer require symfony/twig-bundle".');
        }

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $defaults = $config['defaults'];
        if ($defaults === [self::DEPRECATED_DEFAULT_KEY]) {
            trigger_deprecation('symfony/ux-twig-component', '2.13', 'Not setting the "twig_component.defaults" config option is deprecated. Check the documentation for an example configuration.');
            $container->setParameter('ux.twig_component.legacy_autonaming', true);

            $defaults = [];
        }
        $container->setParameter('ux.twig_component.component_defaults', $defaults);

        $container->registerAttributeForAutoconfiguration(
            AsTwigComponent::class,
            static function (ChildDefinition $definition, AsTwigComponent $attribute) {
                $definition->addTag('twig.component', array_filter($attribute->serviceConfig()));
            }
        );

        $container
            ->getDefinition('ux.twig_component.component_template_finder')
            ->replaceArgument(1,  $config['anonymous_template_directory'])
        ;

        $container
            ->getDefinition('ux.twig_component.command.debug')
            ->replaceArgument(3, $config['anonymous_template_directory'])
        ;

        if ($container->getParameter('kernel.debug')) {
            $loader->load('debug.php');
        }
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('twig_component');
        $rootNode = $treeBuilder->getRootNode();
        \assert($rootNode instanceof ArrayNodeDefinition);

        $rootNode
            ->children()
                ->scalarNode('controllers_json')
                    ->defaultValue('%kernel.project_dir%/assets/controllers.json')
                ->end()
            ->end();

        $this->addDefaultsSection($rootNode);
        $this->addBundleSection($rootNode);

        return $treeBuilder;
    }

    private function addDefaultsSection(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->validate()
            ->always(function ($v) {
                if (!isset($v['anonymous_template_directory'])) {
                    trigger_deprecation('symfony/twig-component-bundle', '2.13', 'Not setting the "twig_component.anonymous_template_directory" config option is deprecated. It will default to "components" in 3.0.');
                    $v['anonymous_template_directory'] = null;
                }

                return $v;
            })
            ->end()
            ->children()
                ->arrayNode('defaults')
                    ->defaultValue([self::DEPRECATED_DEFAULT_KEY])
                    ->useAttributeAsKey('namespace')
                    ->validate()
                        ->always(function ($v) {
                            foreach ($v as $namespace => $defaults) {
                                if (!str_ends_with($namespace, '\\')) {
                                    throw new InvalidConfigurationException(sprintf('The twig_component.defaults namespace "%s" is invalid: it must end in a "\"', $namespace));
                                }
                            }

                            return $v;
                        })
                    ->end()
                    ->arrayPrototype()
                        ->beforeNormalization()
                            ->ifString()
                            ->then(function (string $v) {
                                return ['template_directory' => $v];
                            })
                        ->end()
                        ->children()
                            ->scalarNode('template_directory')
                                ->defaultValue('components')
                            ->end()
                            ->scalarNode('name_prefix')
                                ->defaultValue('')
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('anonymous_template_directory')
                    ->info('Defaults to `components`')
                ->end()
            ->end();
    }

    private function addBundleSection(ArrayNodeDefinition $rootNode): void
    {
        # AcmeFoo
            #  templates: @AcmeFoo/components
            #  namespace: Acme\FooBundle\Twig\Components
            #  anonymous: true
            #  anonymous: false
            #  anonymous: @AcmeFoo/components



        # Component: <twig:AcmeFoo:Bar />
        # Template:  @AcmeFoo/components/Bar.html.twig
        # Class:     Acme\FooBundle\Twig\Components\Bar

        $rootNode
            ->children()
                ->arrayNode('bundles')
                    ->normalizeKeys(false)
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('templates')
                                ->defaultValue('components')
                                ->info('The directory where the components templates are stored')
                            ->end()
                            ->scalarNode('namespace')
                                ->defaultValue('\\AcmeDemo\\Twig\\Components\\')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }

    public function getConfiguration(array $config, ContainerBuilder $container): ConfigurationInterface
    {
        return $this;
    }
}
