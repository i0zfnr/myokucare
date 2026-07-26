<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->timestamp('profile_reviewed_at')->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->dropColumn('profile_reviewed_at');
        });
    }
};
