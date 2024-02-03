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

use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class UXIconNodeVisitor extends AbstractNodeVisitor
{
    public function __construct(
        private string $componentName,
        private string $functionName,
    )
    {
    }

    protected function doEnterNode(Node $node, Environment $env): Node
    {
        return $node;
    }

    /**
     * Called after child nodes are visited.
     *
     * @return Node|null The modified node or null if the node must be removed
     */
    protected function doLeaveNode(Node $node, Environment $env): ?Node
    {
        if ($node instanceof FunctionExpression) {
            // @todo a lot of checks
            if ('component' === $node->getAttribute('name')) {
                if (!$node->hasNode('arguments')) {
                    return $node;
                }
                $arguments = $node->getNode('arguments');
                if (!$arguments->hasNode(0)) {
                    return $node;
                }
                $name = $arguments->getNode(0);
                if (!$name instanceof ConstantExpression) {
                    return $node;
                }
                if ($name->getAttribute('value') !== $this->componentName) {
                    return $node;
                }

                if (!$arguments->hasNode(1)) {
                    return $node;
                }
                $arguments = $arguments->getNode(1);

                $node->setAttribute('name', $this->functionName);
                // @todo a lot of checks
                $name->setAttribute('value', $arguments->getNode(1)->getAttribute('value'));
                $arguments->removeNode(0);
                $arguments->removeNode(1);

                return $node;
            }
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 0;
    }
}
