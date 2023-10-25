<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Twig;

use Twig\Compiler;
use Twig\Node\EmbedNode;
use Twig\Node\Expression\AbstractExpression;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ComponentNode extends EmbedNode
{
    /**
     * @param string $component The component name (ie: Button, Foo:Bar)
     * @param string $template The template path (ie: components/Button.html.twig)
     * @param string $tag
     */
    public function __construct(
        string $component,
        string $template,
        int $index,
        ?AbstractExpression $variables,
        bool $only,
        int $lineno,
        string $tag
    )
    {
        parent::__construct($template, $index, $variables, $only, false, $lineno, $tag);

        $this->setAttribute('component', $component);
        $this->setAttribute('lineno', $lineno);
    }

    public function compile(Compiler $compiler): void
    {
        $compiler->addDebugInfo($this);

        $compiler
            ->write('$template = $this->extensions[')
            ->string(ComponentExtension::class)
            ->raw(']->getTemplate(')
            // TODO
            // ->subcompile($this->getAttribute('expr'))
            ->string($this->getAttribute('component'))
            // ->subcompile($this->getNode('name'))
            ->raw(");\n");
            //->string($this->getAttribute('component'))

        $compiler
            ->write('$preRendered = $this->extensions[')
            ->string(ComponentExtension::class)
            ->raw(']->preRender(')
            ->string($this->getAttribute('component'))
            ->raw(', ');
        if (!$this->hasNode('variables')) {
            $compiler->raw('$content');
        } else {
            $compiler
                ->raw('twig_to_array(')
                ->subcompile($this->getNode('variables'))
                ->raw(')');
        }
        $compiler->raw(");\n");

        $compiler
            ->write('if (null !== $preRendered) {')
            ->raw("\n")
            ->indent()
            ->write('echo $preRendered;')
            ->raw("\n")
            ->outdent()
            ->write('} else {')
            ->raw("\n")
            ->indent()
        ;

        $compiler
            ->write('$embeddedContext = $this->extensions[')
            ->string(ComponentExtension::class)
            ->raw(']->embeddedContext(')
            ->string($this->getAttribute('component'))
            ->raw(', ');
        if (!$this->hasNode('variables')) {
            $compiler->raw('$content');
        } else {
            $compiler
                ->raw('twig_to_array(')
                ->subcompile($this->getNode('variables'))
                ->raw(')');
        }
        $compiler
            ->raw(', ')
            ->raw($this->getAttribute('only') ? '[]' : '$context')
            ->raw(', ')
            // ->string(TemplateNameParser::parse($this->getAttribute('name')))
            ->raw('$template')
            ->raw(', ')
            ->raw($this->getAttribute('index'))
            ->raw(");\n")
        ;

        $compiler->write('$embeddedBlocks = $embeddedContext[')
            ->string('outerBlocks')
            ->raw(']->convert($blocks, ')
            ->raw($this->getAttribute('index'))
            ->raw(");\n")
        ;

        $this->addGetTemplate($compiler);
        $compiler->raw('->display($embeddedContext, $embeddedBlocks);');
        $compiler->raw("\n");

        $compiler->write('$this->extensions[')
            ->string(ComponentExtension::class)
            ->raw(']->finishEmbeddedComponentRender()')
            ->raw(";\n")
        ;

        $compiler
            ->outdent()
            ->write('}')
            ->raw("\n")
        ;
    }

    protected function addGetTemplate(Compiler $compiler): void
    {
        $compiler
            ->write('$this->loadTemplate(')
            ->raw('$template')
            ->raw(', ')
            ->repr($this->getTemplateName())
            ->raw(', ')
            ->repr($this->getAttribute('lineno'))
            ->raw(', ')
            ->string($this->getAttribute('index'))
            ->raw(')');
    }
}
