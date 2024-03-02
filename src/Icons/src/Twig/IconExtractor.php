<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\Icons\Twig;

use Symfony\Bundle\TwigBundle\TemplateIterator;
use Symfony\Component\Finder\Finder;
use Twig\Cache\NullCache;
use Twig\Environment;
use Twig\Loader\ChainLoader;
use Twig\Loader\FilesystemLoader;
use Twig\Loader\LoaderInterface;

/**
 * @author Simon André <smn.andre@gmail.com>
 *
 * @internal
 */
final class IconExtractor
{
    public function __construct(
        private readonly Environment $twig,
        private readonly TemplateIterator $templateIterator,
    ) {
    }

    public function extractIcons(): array
    {
        $icons = [];

        foreach ($this->templateIterator as $templatePath) {
            $icons[$templatePath] = $this->extractTemplateIcons($templatePath);
        }

        foreach ($this->files($this->twig->getLoader()) as $file) {
            $icons[$file] = $this->extractTemplateIcons($file);
        }

        return [...$icons];
    }

    private function extractTemplateIcons(string $templatePath): array
    {
        $visitor = $this->twig->getExtension(UXIconExtension::class)->getNodeVisitors()[0];

        $cache = $this->twig->getCache();
        $this->twig->setCache(new NullCache());

        $visitor->enable();
        try {
            $this->twig->load($templatePath);
        } catch (\Exception $e) {
            echo $e->getMessage() . PHP_EOL;
        }
        $icons = $visitor->getIcons();
        $visitor->disable();

        $this->twig->setCache($cache);

        return $icons;
    }

    /**
     * @return string[]
     */
    private function files(LoaderInterface $loader): iterable
    {
        $files = [];

        if ($loader instanceof FilesystemLoader) {
            foreach ($loader->getNamespaces() as $namespace) {
                foreach ($loader->getPaths($namespace) as $path) {
                    foreach ((new Finder())->files()->in($path)->name('*.twig') as $file) {
                        $file = (string) $file;
                        if (!\in_array($file, $files, true)) {
                            yield $file;
                        }

                        $files[] = $file;
                    }
                }
            }
        }

        if ($loader instanceof ChainLoader) {
            foreach ($loader->getLoaders() as $subLoader) {
                yield from $this->files($subLoader);
            }
        }
    }
}
