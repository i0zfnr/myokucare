<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_interests', function (Blueprint $table) {
            $table->timestamp('profile_shared_at')->nullable()->after('application_date');
            $table->index(['job_id', 'profile_shared_at']);
        });
    }

    public function down(): void
    {
        Schema::table('job_interests', function (Blueprint $table) {
            $table->dropIndex(['job_id', 'profile_shared_at']);
            $table->dropColumn('profile_shared_at');
        });
    }
};
