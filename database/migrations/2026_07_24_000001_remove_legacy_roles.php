<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Convert legacy role users to oku_user before altering the column
        User::query()->whereIn('role', ['family_member', 'viewer'])->update(['role' => 'oku_user']);

        // Only MySQL needs a raw ALTER; SQLite/RefreshDatabase picks up the
        // updated enum definition from the original migration automatically.
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','jkm_officer','employer','oku_user') NOT NULL DEFAULT 'oku_user'");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('super_admin','jkm_officer','employer','oku_user','family_member','viewer') NOT NULL DEFAULT 'viewer'");
        }
    }
};
