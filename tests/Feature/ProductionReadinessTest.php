<?php

namespace Tests\Feature;

use App\Services\ProductionReadinessService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductionReadinessTest extends TestCase
{
    use RefreshDatabase;

    public function test_local_environment_is_blocked_from_production_deployment(): void
    {
        $this->artisan('deployment:check')
            ->expectsOutputToContain('Deployment is blocked')
            ->assertFailed();
    }

    public function test_readiness_audit_detects_unsafe_session_and_delivery_configuration(): void
    {
        config()->set('session.encrypt', false);
        config()->set('session.secure', false);
        config()->set('session.lifetime', 120);
        config()->set('mail.default', 'log');
        config()->set('queue.default', 'sync');
        config()->set('database.default', 'sqlite');

        $failures = collect(app(ProductionReadinessService::class)->failures())->keyBy('name');

        foreach (['session_encryption', 'secure_cookie', 'session_lifetime', 'mail', 'queue', 'database'] as $name) {
            $this->assertArrayHasKey($name, $failures);
        }
    }

    public function test_private_storage_and_notification_schema_pass_the_release_gate_checks(): void
    {
        $checks = collect(app(ProductionReadinessService::class)->checks())->keyBy('name');

        $this->assertTrue($checks['private_storage']['passed']);
        $this->assertTrue($checks['notifications_table']['passed']);
        $this->assertTrue($checks['push_subscriptions_table']['passed']);
        $this->assertTrue($checks['storage_writable']['passed']);
        $this->assertTrue($checks['cache_writable']['passed']);
    }
}
