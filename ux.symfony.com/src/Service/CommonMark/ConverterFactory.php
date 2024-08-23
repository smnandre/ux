<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Service\CommonMark;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Extension\ExternalLink\ExternalLinkExtension;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkRenderer;
use League\CommonMark\Extension\Mention\MentionExtension;
use League\CommonMark\Extension\TableOfContents\TableOfContentsExtension;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Tempest\Highlight\CommonMark\HighlightExtension;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
#[AsDecorator('twig.markdown.league_common_mark_converter_factory')]
final class ConverterFactory
{
    public function __invoke(): CommonMarkConverter
    {
        $converter = new CommonMarkConverter([
            'mentions' => [
                'github_handle' => [
                    'prefix' => '@',
                    'pattern' => '[a-z\d](?:[a-z\d]|-(?=[a-z\d])){0,38}(?!\w)',
                    'generator' => 'https://github.com/%s',
                ],
                'github_issue' => [
                    'prefix' => '#',
                    'pattern' => '\d+',
                    'generator' => 'https://github.com/symfony/ux/issues/%d',
                ],
            ],
            'external_link' => [
                'internal_hosts' => ['/(^|\.)symfony\.com$/'],
            ],
            'table_of_contents' => [
                'html_class' => 'table-of-contents',
                'position' => 'top',
                // 'min_heading_level' => 2,
                'max_heading_level' => 3,
            ],
            'heading_permalink' => [
                // 'html_class' => 'heading-permalink',
                //'id_prefix' => 'content',
                'apply_id_to_heading' => true,
                // 'heading_class' => '',
                // 'fragment_prefix' => 'content',
                // 'insert' => 'none',
                'min_heading_level' => 1,
                // 'max_heading_level' => 6,
                // 'title' => 'Permalink',
                //'symbol' => HeadingPermalinkRenderer::DEFAULT_SYMBOL,
            ],
        ]);

        $converter->getEnvironment()
            ->addExtension(new ExternalLinkExtension())
            ->addExtension(new MentionExtension())
            ->addExtension(new HighlightExtension())
            ->addExtension(new FrontMatterExtension())
            ->addExtension(new GithubFlavoredMarkdownExtension())
            ->addExtension(new HeadingPermalinkExtension())
            ->addExtension(new TableOfContentsExtension())
        ;

        return $converter;
    }
}
