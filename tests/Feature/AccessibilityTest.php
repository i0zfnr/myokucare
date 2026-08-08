<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_authentication_pages_expose_landmarks_labels_and_live_errors(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('href="#login-form"', false)
            ->assertSee('<main class="login-page">', false)
            ->assertSee('for="email"', false)
            ->assertSee('for="password"', false);

        $this->followingRedirects()->from(route('login'))->post(route('login.store'), [])
            ->assertSee('role="alert"', false)
            ->assertSee('aria-live="assertive"', false);
    }

    public function test_authenticated_layout_has_skip_navigation_and_accessible_status_updates(): void
    {
        $user = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($user)->withSession(['success' => 'Saved'])->get(route('dashboard'))
            ->assertOk()
            ->assertSee('href="#main"', false)
            ->assertSee('id="main"', false)
            ->assertSee('role="status" aria-live="polite"', false)
            ->assertSee('aria-label="Notifikasi', false);
    }

    public function test_reduced_motion_and_visible_keyboard_focus_are_defined(): void
    {
        $css = file_get_contents(resource_path('css/app.css'))
            .file_get_contents(resource_path('css/auth.css'))
            .file_get_contents(resource_path('css/landing.css'));

        $this->assertStringContainsString(':focus-visible', $css);
        $this->assertStringContainsString('prefers-reduced-motion', $css);
        $this->assertStringContainsString('.high-contrast :focus-visible', $css);
    }
}
