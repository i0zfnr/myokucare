<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('mykad_verification_status', 40)->default('NOT_SUBMITTED')->after('oku_id');
            $table->timestamp('mykad_submitted_at')->nullable();
            $table->timestamp('mykad_verified_at')->nullable();
            $table->uuid('mykad_verification_session_id')->nullable();
            $table->string('mykad_review_reason')->nullable();
            $table->boolean('mykad_resubmission_required')->default(false);
        });

        Schema::create('verification_sessions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('status', 40)->default('SUBMISSION_IN_PROGRESS');
            $table->timestamp('expires_at');
            $table->timestamp('consent_accepted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
        });

        Schema::create('verification_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->constrained('verification_sessions')->cascadeOnDelete();
            $table->string('document_type', 30);
            $table->text('original_file_path');
            $table->text('processed_file_path')->nullable();
            $table->string('quality_status', 30)->default('PENDING');
            $table->decimal('quality_score', 5, 4)->nullable();
            $table->json('quality_issues')->nullable();
            $table->json('processing_metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['session_id', 'document_type']);
        });

        Schema::create('extracted_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('verification_documents')->cascadeOnDelete();
            $table->string('field_name', 50);
            $table->text('encrypted_value');
            $table->string('masked_value')->nullable();
            $table->decimal('confidence', 5, 4);
            $table->string('source', 20);
            $table->timestamps();
            $table->unique(['document_id', 'field_name']);
        });

        Schema::create('qr_scan_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('verification_documents')->cascadeOnDelete();
            $table->text('encrypted_payload');
            $table->string('payload_type', 30);
            $table->decimal('detection_confidence', 5, 4)->nullable();
            $table->string('provider_status', 40)->default('UNVERIFIED_EXTERNAL_DATA');
            $table->timestamps();
        });

        Schema::create('identity_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->unique()->constrained('verification_sessions')->cascadeOnDelete();
            $table->boolean('nric_match');
            $table->boolean('name_match');
            $table->decimal('name_similarity', 5, 4);
            $table->string('result', 40);
            $table->json('reason_codes')->nullable();
            $table->text('normalised_values')->nullable();
            $table->timestamps();
        });

        Schema::create('manual_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('session_id')->unique()->constrained('verification_sessions')->cascadeOnDelete();
            $table->string('status', 30)->default('PENDING');
            $table->json('reason_codes');
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manual_reviews');
        Schema::dropIfExists('identity_comparisons');
        Schema::dropIfExists('qr_scan_results');
        Schema::dropIfExists('extracted_fields');
        Schema::dropIfExists('verification_documents');
        Schema::dropIfExists('verification_sessions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'mykad_verification_status', 'mykad_submitted_at', 'mykad_verified_at',
                'mykad_verification_session_id', 'mykad_review_reason', 'mykad_resubmission_required',
            ]);
        });
    }
};
