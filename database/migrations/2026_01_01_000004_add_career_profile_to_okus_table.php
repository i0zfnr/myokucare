<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->text('career_summary')->nullable()->after('assistance_type');
            $table->text('skills')->nullable()->after('career_summary');
            $table->enum('availability_status', ['Mencari Kerja', 'Sudah Bekerja', 'Tidak Tersedia'])->default('Mencari Kerja')->after('skills');
            $table->string('resume_path', 2048)->nullable()->after('availability_status');
            $table->string('oku_card_image_path', 2048)->nullable()->after('resume_path');
            $table->enum('verification_status', ['Pending', 'Verified', 'Rejected'])->default('Pending')->after('oku_card_image_path');
            $table->text('verification_notes')->nullable()->after('verification_status');
            $table->timestamp('verified_at')->nullable()->after('verification_notes');
            $table->foreignId('verified_by')->nullable()->after('verified_at')->constrained('users')->nullOnDelete();
            $table->index(['availability_status', 'verification_status']);
        });
    }

    public function down(): void
    {
        Schema::table('okus', function (Blueprint $table) {
            $table->dropIndex(['availability_status', 'verification_status']);
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn([
                'career_summary',
                'skills',
                'availability_status',
                'resume_path',
                'oku_card_image_path',
                'verification_status',
                'verification_notes',
                'verified_at',
            ]);
        });
    }
};
