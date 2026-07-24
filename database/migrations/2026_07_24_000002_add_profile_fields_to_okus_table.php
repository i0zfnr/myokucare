<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->string('sektor_pekerjaan')->nullable()->after('employment_status');
            $table->json('jenis_bantuan')->nullable()->after('assistance_type');
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `okus` MODIFY `oku_category` ENUM('Fizikal', 'Penglihatan', 'Pendengaran', 'Pertuturan', 'Pembelajaran', 'Mental', 'Pelbagai') NOT NULL");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `okus` MODIFY `oku_category` ENUM('Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan') NOT NULL");
        }

        Schema::table('okus', function (Blueprint $table) {
            $table->dropColumn(['sektor_pekerjaan', 'jenis_bantuan']);
        });
    }
};
