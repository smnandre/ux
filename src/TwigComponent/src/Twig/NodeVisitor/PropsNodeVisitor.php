<?php

namespace Symfony\UX\TwigComponent\Twig\NodeVisitor;

use Symfony\UX\TwigComponent\Twig\PropsNode;
use Twig\Environment;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\NodeVisitorInterface;

/**
 * @author Simon André<smn.andre@gmail.com>
 *
 * @internal
 */
final class PropsNodeVisitor implements NodeVisitorInterface
{
    public function __construct(
        private PropsCollector $propsCollector,
    ) {
    }

    public function enterNode(Node $node, Environment $env): Node
    {
        // if ($node::class === PropsNode::class) {
        //     $this->propsCollector->collect($node->getTemplateName().'_pre' ?? '_'.rand(0, 9999), [
        //         'names' => $node->getAttribute('names'),
        //         'line' => $node->getTemplateLine(),
        //         'path' => $node->getSourceContext()?->getPath(),
        //     ]);
        // }
        //
        // return $node;
        return $node;
    }
    public function leaveNode(Node $node, Environment $env): Node
    {
        if ($node::class === PropsNode::class) {

            foreach ($node->getIterator() as $name => $prop) {
                if ($prop instanceof ConstantExpression) {
                    $this->propsCollector->collect(($prop->getTemplateName() ?? '..').$name, [
                        'name' => $name,
                        'value' => $prop->getAttribute('value'),
                        'line' => $prop->getTemplateLine(),
                        'source' => $prop->getSourceContext()?->getName(),
                        'path' => $prop->getSourceContext()?->getPath(),
                    ]);
                }
            }

            $this->propsCollector->collect($node->getTemplateName() ?? '..', [
                'names' => $node->getAttribute('names'),
                'line' => $node->getTemplateLine(),
                'source' => $node->getSourceContext()?->getName(),
                'path' => $node->getSourceContext()?->getPath(),
            ]);
        }

        return $node;
    }

    public function getPriority(): int
    {
        return 50;
    }
}
