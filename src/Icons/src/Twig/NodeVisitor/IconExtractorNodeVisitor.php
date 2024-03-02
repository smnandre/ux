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
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\FunctionExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class IconExtractorNodeVisitor extends AbstractNodeVisitor
{
    private bool $enabled = false;

    private string $functionName = 'ux_icon';

    private array $icons = [];

    public function enable(): void
    {
        $this->icons = [];
        $this->enabled = true;
    }

    public function disable(): void
    {
        $this->enabled = false;
    }

    public function setFunctionName(string $functionName): void
    {
        $this->functionName = $functionName;
    }

    public function getIcons(): array
    {
        return $this->icons;
    }

    public function getPriority(): int
    {
        return -256;
    }

    protected function doEnterNode(Node $node, Environment $env): Node
    {
        if (!$this->enabled) {
            return $node;
        }

        if (!$node instanceof FunctionExpression) {
            return $node;
        }

        // Twig Function: ux_icon('foo')
        if ($this->functionName === $node->getAttribute('name')) {
            $icon = [
                'nodeType' => 'FunctionExpression',
                'nodeName' => $this->functionName,
                'templateName' => $node->getTemplateName(),
                'templateLine' => $node->getTemplateLine(),
            ];
            $valueNode = $node->getNode('arguments')->getNode(0);

            $this->icons[] = [...$icon, ...$this->extractIconValue($valueNode)];

            return $node;
        }

        // Twig Component: component('UX:Icon', {'name': 'foo'})
        // HTML Component: <twig:UX:Icon name="foo" />
        if ('component' === $node->getAttribute('name')) {
            $componentNameNode = $node->getNode('arguments')->getNode(0);
            if ('UX:Icon' !== $componentNameNode->getAttribute('value')) {
                return $node;
            }

            $icon = [
                'nodeType' => 'FunctionExpression',
                'nodeName' => 'component',
                'templateName' => $node->getTemplateName(),
                'templateLine' => $node->getTemplateLine(),
            ];

            $componentArguments = $node->getNode('arguments')->getNode(1);
            if ($componentArguments instanceof ArrayExpression) {
                foreach ($componentArguments->getKeyValuePairs() as $keyValuePair) {
                    if ($keyValuePair['key']->getAttribute('value') === 'name') {
                        $valueNode = $keyValuePair['value'];

                        $this->icons[] = [...$icon, ...$this->extractIconValue($valueNode)];

                        return $node;
                    }
                }
            }
        }

        return $node;
    }

    protected function doLeaveNode(Node $node, Environment $env): ?Node
    {
        return $node;
    }

    /**
     * @return array{iconType: string, iconName: string|null}
     */
    private function extractIconValue(Node $valueNode): array
    {
        if ($valueNode instanceof ConstantExpression) {
            return [
                'iconType' => 'ConstantExpression',
                'iconName' => $valueNode->getAttribute('value'),
            ];
        }
        if ($valueNode instanceof NameExpression) {
            return [
                'iconType' => 'NameExpression',
                'iconName' => $valueNode->getAttribute('name'),
            ];
        }

        return [
            'iconType' => substr($valueNode::class, strrpos($valueNode::class, '\\') + 1),
            'iconName' => ($valueNode->hasAttribute('name') ? $valueNode->getAttribute('name') : null)
                ?? ($valueNode->hasAttribute('value') ? $valueNode->getAttribute('value') : null),
        ];
        // $valueNode->getSourceContext()->getCode()
    }
}
