<?php

namespace App\Console\Commands;

use App\Services\ProductionReadinessService;
use Illuminate\Console\Command;

class CheckProductionReadiness extends Command
{
    protected $signature = 'deployment:check';

    protected $description = 'Audit production configuration without exposing credentials';

    public function handle(ProductionReadinessService $readiness): int
    {
        $checks = $readiness->checks();
        $this->table(
            ['Check', 'Result', 'Required action'],
            array_map(fn (array $check) => [
                $check['name'],
                $check['passed'] ? 'PASS' : 'FAIL',
                $check['passed'] ? '-' : $check['message'],
            ], $checks),
        );

        $failures = array_filter($checks, fn (array $check) => ! $check['passed']);
        if ($failures) {
            $this->error(count($failures).' production readiness check(s) failed. Deployment is blocked.');

            return self::FAILURE;
        }

        $this->info('All automated production readiness checks passed. Continue with UAT, backup restore, and smoke testing.');

        return self::SUCCESS;
    }
}
