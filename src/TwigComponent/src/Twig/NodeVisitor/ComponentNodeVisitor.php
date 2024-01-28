<?php

/*
 * This file is part of Twig.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Twig\NodeVisitor;

use Symfony\UX\TwigComponent\Twig\ComponentNode;
use Twig\Environment;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * Transform component tags into component functions.
 *
 * When a component Node does not have a body, it is replaced by a call to the
 * component function.
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class ComponentNodeVisitor implements NodeVisitorInterface
{

    public function enterNode(Node $node, Environment $env): Node
    {
        $this->enterOptimizeFor($node);
        return $node;
    }

    public function leaveNode(Node $node, Environment $env): ?Node
    {
        return $node;
    }

    /**
     * Optimizes "for" tag by removing the "loop" variable creation whenever possible.
     */
    private function enterOptimizeFor(Node $node): void
    {
        if (!$node instanceof ComponentNode) {
            return;
        }

        dd($node, self::class);

        // if (!$node->hasNode('body')) {
        //     return new FunctionExpression('component', $arguments, $node->getTemplateLine());
        // }
        //
        // array_unshift($this->loopsTargets, $node->getNode('key_target')->getAttribute('name'));
    }

    public function getPriority(): int
    {
        return 150;
    }
}
