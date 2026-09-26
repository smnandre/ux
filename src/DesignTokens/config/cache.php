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

/*
 * Imported only with FrameworkBundle, which provides the cache.system pool.
 * Without it, the resolver receives no pool and resolves on every call.
 */
return static function (ContainerConfigurator $container): void {
    $container->services()
        ->set('.ux_design_tokens.cache')
            ->parent('cache.system')
            ->private()
            ->tag('cache.pool')
    ;
};
