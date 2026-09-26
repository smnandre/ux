<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\DesignTokens\Tests\Examples;

use PHPUnit\Framework\TestCase;
use Symfony\UX\DesignTokens\Generator\CssGenerator;
use Symfony\UX\DesignTokens\Generator\GeneratorInterface;
use Symfony\UX\DesignTokens\Tests\Fixtures\Registries;

final class ExamplesTest extends TestCase
{
    public function testTheShippedThemeWritesOnlyItsDarkDifferences(): void
    {
        $registry = Registries::fromFiles(resolverPath: \dirname(__DIR__, 2).'/examples/theme/theme.resolver.json');
        $light = $registry->flatten(['scheme' => 'light']);
        $dark = $registry->flatten(['scheme' => 'dark']);

        self::assertCount(78, $light);
        self::assertCount(20, array_filter($light, static fn ($token, string $path): bool => (string) $token !== (string) $dark[$path], \ARRAY_FILTER_USE_BOTH));

        $css = new CssGenerator('dt')->generate($registry->all(['scheme' => 'light']), [GeneratorInterface::DARK_TOKENS => $registry->all(['scheme' => 'dark'])]);
        [$root, $darkBlock] = explode(':root[data-theme="dark"] {', $css, 2);

        self::assertStringContainsString('--dt-dimension-spacing-md: 1rem;', $root);

        // 18 colors, plus the shorthand and the color of the 2 borders.
        self::assertSame(22, substr_count($darkBlock, '--dt-'));
        self::assertStringContainsString('--dt-color-palette-surface: color(srgb 0.1294 0.1451 0.1608);', $darkBlock);
        self::assertStringContainsString('--dt-border-default-color:', $darkBlock);
        self::assertStringNotContainsString('--dt-dimension-spacing-md', $darkBlock);
    }

    public function testContextualThemeExample(): void
    {
        $output = self::runExample('contextual-theme.php');

        self::assertStringContainsString("brand: symfony, sky (default symfony)\n", $output);
        self::assertStringContainsString("scheme: light, dark (default light)\n", $output);
        self::assertStringContainsString("primary: color(srgb 0.3255 0.5176 0.9294)\n", $output);
        self::assertStringContainsString("ui-font: system-ui, sans-serif\n", $output);
        self::assertStringContainsString("brand-source: brand-sky.tokens.json\n", $output);
    }

    public function testJavaScriptExportExample(): void
    {
        $output = self::runExample('export-javascript.php');

        self::assertStringContainsString('export const tokens = Object.freeze(JSON.parse(', $output);
        self::assertStringContainsString('\\"color.action.primary\\":\\"color(srgb 0.3255 0.5176 0.9294)\\"', $output);
        self::assertStringContainsString('\\"color.surface.canvas\\":\\"color(srgb 0.1294 0.1451 0.1608)\\"', $output);
        self::assertStringContainsString('\\"font.size.h1\\":\\"3.25rem\\"', $output);
        self::assertStringContainsString('\\"dimension.spacing.lg\\":\\"1.5rem\\"', $output);
        self::assertStringContainsString('\\"motion.control\\":\\"150ms cubic-bezier(0.2, 0, 0, 1) 0ms\\"', $output);
    }

    private static function runExample(string $file): string
    {
        return (static function () use ($file): string {
            ob_start();

            try {
                require \dirname(__DIR__, 2).'/examples/'.$file;

                return (string) ob_get_contents();
            } finally {
                ob_end_clean();
            }
        })();
    }
}
