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

use Symfony\UX\DesignTokens\Generator\JavaScriptGenerator;
use Symfony\UX\DesignTokens\Resolver\ConfiguredTokenResolver;
use Symfony\UX\DesignTokens\TokenRegistry;

$tokens = new TokenRegistry(new ConfiguredTokenResolver(resolverPath: __DIR__.'/theme/theme.resolver.json'));

echo new JavaScriptGenerator()->generate($tokens->all(['brand' => 'sky', 'scheme' => 'dark']));
