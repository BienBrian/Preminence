<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_returns_200(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_login_page_returns_200(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    public function test_people_page_returns_200(): void
    {
        $response = $this->get('/people');
        $response->assertStatus(200);
    }

    public function test_noticeboard_page_returns_200(): void
    {
        $response = $this->get('/noticeboard');
        $response->assertStatus(200);
    }

    public function test_events_page_returns_200(): void
    {
        $response = $this->get('/events_seminars');
        $response->assertStatus(200);
    }

    public function test_spiritual_page_returns_200(): void
    {
        $response = $this->get('/spiritual');
        $response->assertStatus(200);
    }

    public function test_articles_page_returns_200(): void
    {
        $response = $this->get('/our_articles');
        $response->assertStatus(200);
    }

    public function test_prayer_wall_page_returns_200(): void
    {
        $response = $this->get('/prayer-wall');
        $response->assertStatus(200);
    }

    // -- Protected pages redirect to login --

    public function test_dashboard_redirects_unauthenticated(): void
    {
        $response = $this->get('/dashboard/home');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_settings_redirects_unauthenticated(): void
    {
        $response = $this->get('/dashboard/settings/general');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_finances_redirects_unauthenticated(): void
    {
        $response = $this->get('/dashboard/finances/overview');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_communication_redirects_unauthenticated(): void
    {
        $response = $this->get('/dashboard/communication/emails');
        $response->assertRedirect('/login');
    }

    public function test_dashboard_profile_redirects_unauthenticated(): void
    {
        $response = $this->get('/dashboard/profile');
        $response->assertRedirect('/login');
    }
}
