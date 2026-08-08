<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->string('job_category', 100)->nullable()->after('title');
            $table->string('workplace_state', 100)->nullable()->after('location');
            $table->string('workplace_district', 100)->nullable()->after('workplace_state');
            $table->string('workplace_mukim', 100)->nullable()->after('workplace_district');
            $table->string('workplace_village')->nullable()->after('workplace_mukim');
            $table->index(['workplace_district', 'job_category', 'is_active'], 'jobs_besut_category_active_index');
        });

        DB::table('jobs')
            ->whereRaw('LOWER(location) LIKE ?', ['%besut%'])
            ->update([
                'workplace_state' => config('jobs.state'),
                'workplace_district' => config('jobs.district'),
            ]);
    }

    public function down(): void
    {
        Schema::table('jobs', function (Blueprint $table) {
            $table->dropIndex('jobs_besut_category_active_index');
            $table->dropColumn(['job_category', 'workplace_state', 'workplace_district', 'workplace_mukim', 'workplace_village']);
        });
    }
};
