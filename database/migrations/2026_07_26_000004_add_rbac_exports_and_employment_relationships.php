<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('users', 'permissions')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('permissions')->nullable()->after('preferences');
            });
        }

        if (! Schema::hasColumn('okus', 'disability_export_consent')) {
            Schema::table('okus', function (Blueprint $table) {
                $table->boolean('disability_export_consent')->default(false);
                $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('deletion_reason')->nullable();
                $table->text('deletion_notes')->nullable();
                $table->string('previous_status')->nullable();
                $table->timestamp('restored_at')->nullable();
                $table->foreignId('restored_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('restore_reason')->nullable();
            });
        }

        if (! Schema::hasColumn('employers', 'deleted_by_user_id')) {
            Schema::table('employers', function (Blueprint $table) {
                $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('deletion_reason')->nullable();
                $table->text('deletion_notes')->nullable();
                $table->string('previous_status')->nullable();
                $table->timestamp('restored_at')->nullable();
                $table->foreignId('restored_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('restore_reason')->nullable();
            });
        }

        if (! Schema::hasColumn('oku_employments', 'employer_id')) {
            Schema::table('oku_employments', function (Blueprint $table) {
                $table->foreignId('employer_id')->nullable()->after('oku_id')->constrained()->restrictOnDelete();
                $table->string('job_title')->nullable()->after('job_id');
                $table->string('department')->nullable();
                $table->string('employment_type')->nullable();
                $table->string('supervisor_name')->nullable();
                $table->text('salary_encrypted')->nullable();
                $table->string('verification_status', 30)->default('PENDING');
                $table->foreignId('verified_by_pegawai_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('verified_at')->nullable();
                $table->foreignId('deleted_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('deletion_reason')->nullable();
                $table->text('deletion_notes')->nullable();
                $table->softDeletes();
            });
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `oku_employments` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'PENDING'");
        }

        DB::table('oku_employments')->orderBy('id')->each(function ($employment): void {
            $job = DB::table('jobs')->find($employment->job_id);
            DB::table('oku_employments')->where('id', $employment->id)->update([
                'employer_id' => $job?->employer_id,
                'job_title' => $job?->title,
                'employment_type' => $job?->employment_type,
                'salary_encrypted' => $employment->salary !== null ? Crypt::encryptString((string) $employment->salary) : null,
                'verification_status' => 'VERIFIED',
                'status' => match ($employment->status) {
                    'Active' => 'ACTIVE',
                    'Terminated' => 'TERMINATED',
                    default => 'INACTIVE',
                },
            ]);
        });
        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE `oku_employments` MODIFY `status` ENUM('PENDING','ACTIVE','INACTIVE','TERMINATED','REJECTED','UNDER_REVIEW') NOT NULL DEFAULT 'PENDING'");
        }

        Schema::create('export_audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('exported_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('exported_by_role', 30);
            $table->string('export_type', 50);
            $table->string('format', 10);
            $table->string('status', 20)->default('PROCESSING');
            $table->unsignedInteger('record_count')->default(0);
            $table->json('filters')->nullable();
            $table->json('fields_included');
            $table->json('sensitive_fields_included')->nullable();
            $table->string('purpose');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('generated_file_path')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamps();
            $table->index(['exported_by_user_id', 'status']);
            $table->index('expires_at');
        });

        Schema::create('record_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('performed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('performed_by_role', 30);
            $table->string('action', 40);
            $table->string('entity_type', 40);
            $table->unsignedBigInteger('entity_id');
            $table->text('previous_data')->nullable();
            $table->text('updated_data')->nullable();
            $table->string('deletion_reason')->nullable();
            $table->text('deletion_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            $table->index(['entity_type', 'entity_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('record_audit_logs');
        Schema::dropIfExists('export_audit_logs');
        Schema::table('oku_employments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employer_id');
            $table->dropConstrainedForeignId('verified_by_pegawai_id');
            $table->dropConstrainedForeignId('deleted_by_user_id');
            $table->dropColumn(['job_title', 'department', 'employment_type', 'supervisor_name', 'salary_encrypted', 'verification_status', 'verified_at', 'deletion_reason', 'deletion_notes', 'deleted_at']);
        });
        foreach (['okus', 'employers'] as $name) {
            Schema::table($name, function (Blueprint $table) use ($name) {
                $table->dropConstrainedForeignId('deleted_by_user_id');
                $table->dropConstrainedForeignId('restored_by_user_id');
                $columns = ['deletion_reason', 'deletion_notes', 'previous_status', 'restored_at', 'restore_reason'];
                if ($name === 'okus') {
                    $columns[] = 'disability_export_consent';
                }
                $table->dropColumn($columns);
            });
        }
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('permissions'));
    }
};
