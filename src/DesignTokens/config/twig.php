<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\UX\DesignTokens\Twig\DesignTokenExtension;
use Symfony\UX\DesignTokens\Twig\DesignTokenRuntime;

/*
 * Imported only when Twig is installed. Reading tokens as typed PHP values
 * needs nothing of Twig, and the package says so: an email, a PDF or a console
 * command is a first-class consumer here. So the template helpers are an
 * integration, not the point of entry.
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.ux_design_tokens.twig_extension', DesignTokenExtension::class)
            ->tag('twig.extension')

        ->set('.ux_design_tokens.twig_runtime', DesignTokenRuntime::class)
            ->args([
                service('.ux_design_tokens.registry'),
                service('.ux_design_tokens.color_scheme'),
                service('.ux_design_tokens.stylesheet_cache'),
                service('asset_mapper')->nullOnInvalid(),
                service('.ux_design_tokens.generator.css'),
            ])
            ->tag('kernel.reset', ['method' => 'reset'])
            ->tag('twig.runtime')
    ;
};
