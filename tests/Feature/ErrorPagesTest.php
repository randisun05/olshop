<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_404_renders_the_branded_error_page_when_debug_is_off(): void
    {
        config(['app.debug' => false]);

        $response = $this->get('/produk/tidak-ada-slug-seperti-ini');

        $response->assertStatus(404);
        $response->assertInertia(fn ($page) => $page->component('Error')->where('status', 404));
    }

    public function test_404_still_shows_debug_page_when_debug_is_on(): void
    {
        config(['app.debug' => true]);

        $response = $this->get('/produk/tidak-ada-slug-seperti-ini');

        $response->assertStatus(404);
        $response->assertDontSee('"component":"Error"', false);
    }
}
