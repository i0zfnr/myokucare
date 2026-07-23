<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->string('job_name')->nullable()->after('employment_status');
            $table->string('assistance_type')->nullable()->after('job_name');
        });
    }

    public function down(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->dropColumn(['job_name', 'assistance_type']);
        });
    }
};
