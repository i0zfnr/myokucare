<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_completed_guideline')->default(false)->after('preferred_language');
            $table->timestamp('guideline_completed_at')->nullable()->after('has_completed_guideline');
            $table->timestamp('last_guideline_viewed_at')->nullable()->after('guideline_completed_at');
            $table->string('guideline_completed_version', 20)->nullable()->after('last_guideline_viewed_at');
        });

        Schema::create('guideline_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 32);
            $table->string('language', 8);
            $table->string('device_type', 16);
            $table->string('guideline_version', 20);
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guideline_activity_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'has_completed_guideline',
                'guideline_completed_at',
                'last_guideline_viewed_at',
                'guideline_completed_version',
            ]);
        });
    }
};
