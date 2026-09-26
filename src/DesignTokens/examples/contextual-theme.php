<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require __DIR__.'/../vendor/autoload.php';

use Symfony\UX\DesignTokens\Resolver\ConfiguredTokenResolver;
use Symfony\UX\DesignTokens\TokenRegistry;

$resolverPath = __DIR__.'/theme/theme.resolver.json';
$resolver = new ConfiguredTokenResolver(resolverPath: $resolverPath);
$tokens = new TokenRegistry($resolver);
$inputs = ['brand' => 'sky', 'scheme' => 'dark'];

foreach ($tokens->getModifiers() as $modifier => ['contexts' => $contexts, 'default' => $default]) {
    echo $modifier.': '.implode(', ', $contexts).' (default '.$default.")\n";
}

$resolution = $resolver->trace($inputs);

echo 'primary: '.$resolution->getTokens()['color']['action']['primary']."\n";
echo 'ui-font: '.$resolution->getTokens()['font']['family']['ui']."\n";
echo 'brand-source: '.basename($resolution->getSource('color.palette.brand')?->uri ?? '')."\n";
