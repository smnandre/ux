<?php

namespace App\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Zenstruck\Browser\Test\HasBrowser;

/**
 * @author Kevin Bond <kevinbond@gmail.com>
 */
final class IconsTest extends KernelTestCase
{
    use HasBrowser;

    /**
     * @test
     */
    public function can_view_icon_index(): void
    {
        $this->browser()
            ->visit('/')
            ->assertSuccessful()
            ->click('Search Icons')
            ->assertSuccessful()
            ->assertOn('/icons')
            ->assertSeeIn('h1', 'Search Icons')
        ;
    }

    /**
     * @test
     */
    public function can_view_pack_details_page(): void
    {
        $this->browser()
            ->visit('/icons/flowbite')
            ->assertSuccessful()
            ->assertSeeIn('h1', 'Flowbite Icons')
        ;
    }

    /**
     * @test
     */
    public function invalid_pack(): void
    {
        $this->browser()
            ->visit('/icons/invalid')
            ->assertStatus(404)
        ;
    }

    /**
     * @test
     */
    public function can_view_icon_details_page(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function invalid_icon(): void
    {
        $this->markTestIncomplete();
    }

    /**
     * @test
     */
    public function icon_as_pack_name_redirects_to_icon_details(): void
    {
        $this->markTestIncomplete();
    }
}
