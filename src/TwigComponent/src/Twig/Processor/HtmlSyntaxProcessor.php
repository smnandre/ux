<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\TwigComponent\Twig\Processor;

/**
 * Transform "HTML syntax" into Twig internal syntax.
 *
 * This pass is **only** about transforming the syntax.
 * Validation and optimization are handled by later passes.
 *
 * Component with self-closing tags
 *      <twig:Foo />
 *
 * Component with closing tags:
 *      <twig:Foo></twig:Foo>
 *
 * Component with attributes:
 *      <twig:Foo bar="foobar" />
 *
 * Component with inner blocks:
 *      <twig:Foo>
 *          <twig:block name="side">
 *          </twig:block>
 *      </twig:Foo>
 *
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class HtmlSyntaxProcessor implements ProcessorInterface
{
    public function supports(string $source): bool
    {
        return str_contains($source, '<twig:');
    }

    public function process(string $source): string
    {
        //$pattern = '#<twig:(?<oname>[a-zA-Z0-9_:]+)|</twig:(?<cname>[a-zA-Z0-9_:]+)>#mux';
        $pattern = '#(<twig:(?<oname>[a-zA-Z0-9_:]+)[^>]*>)|(</twig:(?<cname>[a-zA-Z0-9_:]+)>)#mux';

        preg_match_all($pattern, $source, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);
        if (empty($matches)) {
            return $source;
        }

        $output = '';
        $position = 0;
        $outputs = [];

        $tags = [];
        foreach ($matches as $match) {

            $tags[] = $tag = new HtmlTag(
                html: $html = $match[0][0],
                name: $match['cname'][0] ?? $match['oname'][0],
                startPosition: $start = (int) $match[0][1],
                endPosition: $start + strlen($html),
                isOpening: str_starts_with($html, '<') && !str_starts_with($html, '</'),
                isClosing: str_starts_with($html, '</') || str_ends_with($html, '/>'),
            );

            if ($tag->startPosition > $position) {
                $output .= substr($source, $position, $tag->startPosition - $position);
            }

            if ($tag->isOpening && $tag->isClosing) {
                $args = substr($tag->html, strlen($tag->name) + 6, -2);
                $output .= '{{ component("'. $tag->name .'", {"_args": "'. $args.'"}) }}';
            }
            elseif ($tag->isOpening) {
                $output .= '{% component "'. $tag->name .'" %}';
                // TODO attributes
            }
            elseif ($tag->isClosing) {
                $output .= '{% endcomponent %}';
            }

            $position = $tag->endPosition;
        }

        return $output;
    }

    private function getTag(string $source, int $position): ?HtmlTag
    {
        foreach ($this->tags as $tag) {
            if ($tag->startPosition <= $position && $tag->endPosition >= $position) {
                return $tag;
            }
        }

        return null;
    }

}
