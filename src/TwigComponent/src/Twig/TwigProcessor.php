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

use Symfony\UX\TwigComponent\Twig\Processor\CommentProcessor;
use Symfony\UX\TwigComponent\Twig\Processor\HtmlSyntaxProcessor;
use Symfony\UX\TwigComponent\Twig\Processor\ProcessorInterface;
use Symfony\UX\TwigComponent\Twig\Processor\VerbatimProcessor;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class TwigProcessor implements ProcessorInterface
{
    /**
     * @var ProcessorInterface[]
     */
    private array $processors;

    public function process(string $source): string
    {
        foreach($this->getProcessors() as $processor) {
            $source = $processor->process($source);
        }

        return $source;
    }

     public function supports(string $source): bool
    {
        return true;
    }

    /**
     * @return ProcessorInterface[]
     */
    private function getProcessors(): array
    {
        return $this->processors ??= [
            new VerbatimProcessor(),
            new CommentProcessor(),
            new HtmlSyntaxProcessor(),
        ];
    }
}
