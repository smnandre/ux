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

use Symfony\Component\AssetMapper\Event\PreAssetsCompileEvent;
use Symfony\UX\Icons\Command\DebugIconCommand;
use Symfony\UX\Icons\Command\WarmCacheCommand;
use Symfony\UX\Icons\EventListener\WarmIconCacheOnAssetCompileListener;

return static function (ContainerConfigurator $container): void {
    $container->services()

        ->set('.ux_icons.command.debug', DebugIconCommand::class)
            ->args([
                service('.ux_icons.icon_set_registry'),
                service('.ux_icons.local_svg_icon_registry'),
            ])
            ->tag('console.command')
    ;
};
