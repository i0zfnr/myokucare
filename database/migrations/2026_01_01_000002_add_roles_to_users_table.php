<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['super_admin', 'jkm_officer', 'employer', 'oku_user'])
                ->default('oku_user')->after('password')->index();
            $table->foreignId('employer_id')->nullable()->after('role')->constrained()->nullOnDelete();
            $table->foreignId('oku_id')->nullable()->after('employer_id')->constrained()->nullOnDelete();
            $table->boolean('is_active')->default(true)->after('oku_id');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employer_id');
            $table->dropConstrainedForeignId('oku_id');
            $table->dropColumn(['role', 'is_active', 'last_login_at']);
        });
    }
};
