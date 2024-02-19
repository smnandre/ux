<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\UX\LiveComponent\Tests\Functional\EventListener;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\UX\LiveComponent\Tests\LiveComponentTestHelper;
use Zenstruck\Browser\Test\HasBrowser;

final class LazyLiveComponentSubscriberTest extends KernelTestCase
{
    use HasBrowser;
    use LiveComponentTestHelper;

    public function testLazyComponentIsNotRendered(): void
    {
        $crawler = $this->browser()
            ->visit('/render-template/render_lazy_component')
            ->assertSuccessful()
            ->crawler();

        $div = $crawler->filter('div');

        $this->assertSame('', trim($div->html()));
        $this->assertSame('lazy', $div->attr('loading'));
        $this->assertSame('live:appear->live#$render', $div->attr('data-action'));
    }

    /**
     * @dataProvider provideLazyValues
     */
    public function testLazyComponentRenderingDependsOnLazyValue(mixed $lazy, bool $isRendered): void
    {
        $crawler = $this->browser()
            ->visit('/render-template/render_lazy_component_with_value?lazy='.$lazy)
            ->assertSuccessful();

        $crawler->assertElementCount('#count', $isRendered ? 1 : 0);
        $crawler->assertElementCount('[loading="lazy"]', $isRendered ? 0 : 1);
    }

    public static function provideLazyValues(): iterable
    {
        return [
            [true, false],
            [false, true],
            ['', true],
        ];
    }

    public function testLazyComponentIsRenderedLaterWithInitialData(): void
    {
        $crawler = $this->browser()
            ->visit('/render-template/render_lazy_component')
            ->assertSuccessful()
            ->crawler();

        $componentDiv = $crawler->filter('div');
        $this->assertEmpty(trim($componentDiv->html()));

        $props = json_decode($componentDiv->attr('data-live-props-value'), true);

        $browser = $this->browser()
            ->throwExceptions()
            ->post('/_components/tally_component', [
                'body' => [
                    'data' => json_encode([
                        'props' => $props,
                    ]),
                ],
            ])->assertSuccessful()
        ;

        $browser->assertElementCount('#count', 1);
        $browser->assertElementAttributeContains('#count', 'value', '7');
    }
}
