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

use Twig\Environment;
use Twig\Lexer;
use Twig\Source;
use Twig\TokenStream;

/**
 * @author Mathèo Daninos <matheo.daninos@gmail.com>
 *
 * @internal
 *
 * thanks to @giorgiopogliani for the inspiration on this lexer <3
 *
 * @see https://github.com/giorgiopogliani/twig-components
 */
class ComponentLexer extends Lexer
{
    /**
     * @var array<string, string>
     */
    private readonly array $namespaces;

    /**
     * @param array<string, string> $namespaces Extra namespaces, as namespace => component name prefix
     */
    public function __construct(Environment $env, array $namespaces = [], array $options = [])
    {
        parent::__construct($env, $options);

        $this->namespaces = $namespaces;
    }

    public function tokenize(Source $source): TokenStream
    {
        $preLexer = new TwigPreLexer(1, $this->namespaces);
        $preparsed = $preLexer->preLexComponents($source->getCode());

        return parent::tokenize(
            new Source(
                $preparsed,
                $source->getName(),
                $source->getPath()
            )
        );
    }
}
