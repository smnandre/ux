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

use Symfony\UX\TwigComponent\BlockStack;
use Symfony\UX\TwigComponent\ComponentFactory;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\ConstantExpression;
use Twig\Node\Expression\NameExpression;
use Twig\Node\Node;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Kevin Bond <kevinbond@gmail.com>
 *
 * @internal
 */
final class ComponentTokenParser extends AbstractTokenParser
{
    private array $lineAndFileCounts = [];

    public function parse(Token $token): Node
    {
        $stream = $this->parser->getStream();
        $parent = $this->parser->getExpressionParser()->parseExpression();

        [$variables, $only] = $this->parseArguments();

        $parentToken = $fakeParentToken = new Token(/* Token::STRING_TYPE */ 7, '__parent__', $token->getLine());
        if ($parent instanceof ConstantExpression) {
            $parentToken = new Token(/* Token::STRING_TYPE */ 7, $parent->getAttribute('value'), $token->getLine());
        } elseif ($parent instanceof NameExpression) {
            $parentToken = new Token(/* Token::NAME_TYPE */ 5, $parent->getAttribute('name'), $token->getLine());
        }

        if (null === $variables) {
            $variables = new ArrayExpression([], $parent->getTemplateLine());
        }

        // inject a fake parent to make the parent() function work
        $stream->injectTokens([
            new Token(Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Token(Token::NAME_TYPE, 'extends', $token->getLine()),
            $parentToken,
            new Token(Token::BLOCK_END_TYPE, '', $token->getLine()),

            // // Add an empty block which can act as a fallback for when an outer
            // // block is referenced that is not passed in from the embedded component.
            // See BlockStack::__call()
            new Token(Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Token(Token::NAME_TYPE, 'block', $token->getLine()),
            new Token(Token::NAME_TYPE, BlockStack::OUTER_BLOCK_FALLBACK_NAME, $token->getLine()),
            new Token(Token::BLOCK_END_TYPE, '', $token->getLine()),
            new Token(Token::BLOCK_START_TYPE, '', $token->getLine()),
            new Token(Token::NAME_TYPE, 'endblock', $token->getLine()),
            new Token(Token::BLOCK_END_TYPE, '', $token->getLine()),
        ]);

        $module = $this->parser->parse($stream, fn (Token $token) => $token->test("end{$this->getTag()}"), true);

        // override the parent with the correct one
        if ($fakeParentToken === $parentToken) {
            $module->setNode('parent', $parent);
        }

        $this->parser->embedTemplate($module);

        // use deterministic index for the embedded template, so it can be loaded in a controlled manner
        // $module->setAttribute('index', $this->generateEmbeddedTemplateIndex(TemplateNameParser::parse($stream->getSourceContext()->getName()), $token->getLine()));

        $stream->expect(Token::BLOCK_END_TYPE);

        return new ComponentNode(
            $parentToken->getValue(),
            $module->getTemplateName(),
            $module->getAttribute('index'),
            $variables,
            $only,
            $token->getLine(),
            $this->getTag()
        );
    }

    public function getTag(): string
    {
        return 'component';
    }

    private function parseArguments(): array
    {
        $stream = $this->parser->getStream();

        $variables = null;
        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'with')) {
            $variables = $this->parser->getExpressionParser()->parseExpression();
        }

        $only = false;
        if ($stream->nextIf(/* Token::NAME_TYPE */ 5, 'only')) {
            $only = true;
        }
        $stream->expect(/* Token::BLOCK_END_TYPE */ 3);

        return [$variables, $only];
    }

    private function generateEmbeddedTemplateIndex(string $file, int $line): int
    {
        $fileAndLine = sprintf('%s-%d', $file, $line);
        if (!isset($this->lineAndFileCounts[$fileAndLine])) {
            $this->lineAndFileCounts[$fileAndLine] = 0;
        }

        return crc32($fileAndLine).++$this->lineAndFileCounts[$fileAndLine];
    }
}
