<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent;

use Symfony\UX\StimulusBundle\Dto\StimulusAttributes;
use Symfony\UX\TwigComponent\Escaper\HtmlAttributeEscaperInterface;
use Symfony\UX\TwigComponent\Escaper\TwigHtmlAttributeEscaper;
use Symfony\WebpackEncoreBundle\Dto\AbstractStimulusDto;
use Twig\Environment;
use Twig\Runtime\EscaperRuntime;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
class ComponentAttributesFactory
{
    private readonly HtmlAttributeEscaperInterface $escaper;

    public function __construct(
        private readonly Environment $twig,
    ) {
    }

    public function create(array $attributes = []): ComponentAttributes
    {
        return new ComponentAttributes($attributes, $this->getEscaper());
    }

    private function getEscaper(): HtmlAttributeEscaperInterface
    {
        if (class_exists(EscaperRuntime::class)) {
            $escaper = $this->twig->getRuntime(EscaperRuntime::class);
            if (null !== $escaper) {
                return new TwigHtmlAttributeEscaper($escaper);
            }

            throw new \LogicException(sprintf('The "%s" runtime is not available.', EscaperRuntime::class));
        }

        throw new \LogicException(sprintf('Class "%s" does not exist.', EscaperRuntime::class));
    }

}
