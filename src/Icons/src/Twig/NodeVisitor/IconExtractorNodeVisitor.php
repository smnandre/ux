<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Twig\NodeVisitor;

use Twig\Environment;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class IconExtractorNodeVisitor extends AbstractNodeVisitor
{
    private array $icons = [];

    // private bool $enabled = false;
    //
    // public function enable(): void
    // {
    //    $this->enabled = true;
    //    $this->icons = [];
    // }
    //
    // public function disable(): void
    // {
    //    $this->icons = [];
    //    $this->enabled = false;
    // }

    public function getIcons(): array
    {
        return $this->icons;
    }

    protected function doEnterNode(Node $node, Environment $env): Node
    {
        // if (!$this->enabled) {
        //    return $node;
        // }

        // If node is a function call to ux_icon
        if ($node instanceof FunctionExpression && 'ux_icon' === $node->getAttribute('name')) {
            $nameArgumentNode = $node->getNode('arguments')->getNode(0);

            $this->icons[] = $nameArgumentNode->getAttribute('value');
        }

        // If it's a constant, add it to the icons array

        return $node;
    }

    protected function doLeaveNode(Node $node, Environment $env): ?Node
    {
        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
