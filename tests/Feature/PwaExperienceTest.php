<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PwaExperienceTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_receives_safe_installed_app_navigation(): void
    {
        $expectedRoutes = [
            'super_admin' => ['dashboard', 'admin.users.index', 'admin.audit', 'admin.settings'],
            'jkm_officer' => ['dashboard', 'welfare.index', 'identity-reviews.index', 'admin.settings'],
            'employer' => ['dashboard', 'jobs.index', 'employments.index', 'exports.index'],
            'oku_user' => ['dashboard', 'jobs.index', 'welfare.index', 'career-profile.show'],
        ];

        foreach ($expectedRoutes as $role => $routes) {
            $user = User::factory()->create(['role' => $role, 'is_active' => true]);
            $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

            $response->assertSee('class="pwa-bottom-nav"', false);
            foreach ($routes as $route) {
                $response->assertSee('href="'.route($route).'"', false);
            }
        }
    }

    public function test_permission_dependent_employer_navigation_is_omitted_when_not_allowed(): void
    {
        $user = User::factory()->create([
            'role' => 'employer',
            'permissions' => [],
            'is_active' => true,
        ]);

        $content = $this->actingAs($user)->get(route('dashboard'))->assertOk()->getContent();
        preg_match('/<nav class="pwa-bottom-nav".*?<\/nav>/s', $content, $matches);
        $pwaNavigation = $matches[0] ?? '';

        $this->assertStringNotContainsString('href="'.route('employments.index').'"', $pwaNavigation);
        $this->assertStringContainsString('href="'.route('jobs.index').'"', $pwaNavigation);
    }

    public function test_oku_dashboard_contains_installed_app_next_actions(): void
    {
        $user = User::factory()->create(['role' => 'oku_user', 'is_active' => true]);

        $this->actingAs($user)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('class="pwa-task-panel"', false)
            ->assertSeeText('Tindakan seterusnya')
            ->assertSee('href="'.route('career-profile.show').'"', false)
            ->assertSee('href="'.route('jobs.index').'"', false)
            ->assertSee('href="'.route('welfare.index').'"', false);
    }

    public function test_remaining_dense_tables_use_mobile_card_markup(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin', 'is_active' => true]);
        $this->actingAs($admin);

        foreach (['employments.index', 'exports.index', 'identity-reviews.index', 'deleted-records.index'] as $route) {
            $this->get(route($route))
                ->assertOk()
                ->assertSee('mobile-card-table', false);
        }
    }

    public function test_install_prompt_dismissal_and_online_required_guard_are_present(): void
    {
        $script = file_get_contents(resource_path('js/app.js'));

        $this->assertStringContainsString('myokucare-install-dismissed-v1', $script);
        $this->assertStringContainsString('installDismissalDuration', $script);
        $this->assertStringContainsString('if (!navigator.onLine) updateConnectionStatus();', $script);
        $this->assertStringContainsString("form.method.toLowerCase() === 'get'", $script);
    }
}
