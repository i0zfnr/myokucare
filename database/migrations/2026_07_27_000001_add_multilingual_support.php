<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'preferred_language')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('preferred_language', 10)->default('BM')->after('permissions')->index();
            });
        }

        if (! Schema::hasTable('user_submission_translations')) {
            Schema::create('user_submission_translations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->nullableMorphs('translatable', 'submission_translatable_idx');
                $table->string('field_name', 80);
                $table->text('original_text');
                $table->string('original_language', 10);
                $table->text('translated_text_bm')->nullable();
                $table->text('translated_text_en')->nullable();
                $table->decimal('translation_confidence', 5, 4)->nullable();
                $table->string('provider_status', 40)->default('PROVIDER_UNAVAILABLE');
                $table->timestamp('translated_at')->nullable();
                $table->timestamps();
                $table->unique(['translatable_type', 'translatable_id', 'field_name'], 'submission_translation_unique');
                $table->index(['original_language', 'provider_status'], 'submission_language_status_idx');
            });
        } else {
            Schema::table('user_submission_translations', function (Blueprint $table) {
                $table->index(['translatable_type', 'translatable_id'], 'submission_translatable_idx');
                $table->unique(['translatable_type', 'translatable_id', 'field_name'], 'submission_translation_unique');
                $table->index(['original_language', 'provider_status'], 'submission_language_status_idx');
            });
        }
        if (! Schema::hasColumn('export_audit_logs', 'language')) {
            Schema::table('export_audit_logs', function (Blueprint $table) {
                $table->string('language', 10)->default('BM');
                $table->string('content_mode', 20)->default('TRANSLATED');
            });
        }
    }

    public function down(): void
    {
        Schema::table('export_audit_logs', fn (Blueprint $table) => $table->dropColumn(['language', 'content_mode']));
        Schema::dropIfExists('user_submission_translations');
        if (Schema::hasColumn('users', 'preferred_language')) {
            Schema::table('users', fn (Blueprint $table) => $table->dropColumn('preferred_language'));
        }
    }
};
