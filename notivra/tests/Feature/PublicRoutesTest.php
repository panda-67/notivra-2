<?php

namespace Tests\Feature;

use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_is_accessible(): void
    {
        $response = $this->get('/');

        $response->assertOk();
    }

    public function test_about_page_is_accessible(): void
    {
        $response = $this->get('/about');

        $response->assertOk();
    }

    public function test_gallery_page_is_accessible(): void
    {
        $response = $this->get('/gallery');

        $response->assertOk();
    }

    public function test_services_page_is_accessible(): void
    {
        $response = $this->get('/services');

        $response->assertOk();
    }

    public function test_hire_page_is_accessible(): void
    {
        $response = $this->get('/hire/apply');

        $response->assertOk();
    }

    public function test_nonexistent_route_returns_not_found(): void
    {
        $response = $this->get('/this-route-does-not-exist');

        $response->assertNotFound();
    }
}
