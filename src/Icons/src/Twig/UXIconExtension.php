<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Twig;

use Symfony\UX\Icons\IconRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class UXIconExtension extends AbstractExtension
{
    public function __construct(
        private readonly ?string $componentName = null,
    )
    {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('ux_icon', [IconRenderer::class, 'renderIcon'], ['is_safe' => ['html']]),
        ];
    }

    public function getNodeVisitors(): array
    {
        if (null === $this->componentName) {
            return [];
        }

        return [
            new UXIconNodeVisitor($this->componentName,'ux_icon'),
        ];
    }
}
