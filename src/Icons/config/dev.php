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

use Symfony\UX\Icons\Command\ImportIconCommand;
use Symfony\UX\Icons\Command\ListIconsCommand;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.ux_icons.command.list_icons', ListIconsCommand::class)
            ->args([
                service('.ux_icons.icon_registry'),
            ])
            ->tag('console.command')

        ->set('.ux_icons.command.import_icon', ImportIconCommand::class)
            ->args([
                service('.ux_icons.local_svg_icon_registry'),
                service('http_client')->nullOnInvalid(),
            ])
            ->tag('console.command')
    ;
};
