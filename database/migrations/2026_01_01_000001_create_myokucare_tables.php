<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('okus', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ic_number', 20)->unique();
            $table->enum('gender', ['Lelaki', 'Perempuan']);
            $table->unsignedTinyInteger('age');
            $table->enum('marital_status', ['Berkahwin', 'Bujang', 'Duda', 'Janda']);
            $table->text('address');
            $table->string('education_level', 100);
            $table->string('oku_card_number', 50)->unique();
            $table->enum('oku_category', ['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan']);
            $table->enum('employment_status', ['Bekerja', 'Tidak Bekerja', 'Sendiri'])->default('Tidak Bekerja');
            $table->string('phone_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->boolean('has_smartphone')->default(true);
            $table->boolean('has_internet')->default(false);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['oku_category', 'employment_status']);
            $table->index('age');
        });

        Schema::create('employers', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('registration_number', 50)->unique();
            $table->text('address');
            $table->string('industry_sector', 100);
            $table->string('contact_person');
            $table->string('phone_number', 20);
            $table->string('email')->unique();
            $table->string('website')->nullable();
            $table->text('company_description')->nullable();
            $table->unsignedInteger('number_of_employees')->nullable();
            $table->boolean('has_oku_quota')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('logo_path', 2048)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['industry_sector', 'is_active']);
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employer_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->text('requirements');
            $table->text('responsibilities')->nullable();
            $table->enum('oku_category_suitable', ['Fizikal', 'Pendengaran', 'Mental', 'Pembelajaran', 'Penglihatan', 'Semua']);
            $table->decimal('salary_min', 10, 2);
            $table->decimal('salary_max', 10, 2)->nullable();
            $table->string('location');
            $table->string('working_hours', 100)->nullable();
            $table->enum('employment_type', ['Sepenuh Masa', 'Separuh Masa', 'Kontrak', 'Sementara'])->default('Sepenuh Masa');
            $table->date('application_deadline')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('applications_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['oku_category_suitable', 'is_active']);
            $table->index(['location', 'application_deadline']);
        });

        Schema::create('oku_employments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oku_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->enum('status', ['Active', 'Resigned', 'Terminated', 'Completed'])->default('Active');
            $table->decimal('salary', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['status', 'start_date', 'end_date']);
        });

        Schema::create('welfare_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oku_id')->constrained()->cascadeOnDelete();
            $table->string('application_type', 100);
            $table->enum('status', ['Pending', 'Under Review', 'Approved', 'Rejected'])->default('Pending');
            $table->date('application_date');
            $table->date('review_date')->nullable();
            $table->text('notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->date('next_review_date')->nullable();
            $table->timestamps();
            $table->index(['status', 'application_date']);
            $table->index('review_date');
        });

        Schema::create('review_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('welfare_application_id')->constrained()->cascadeOnDelete();
            $table->date('scheduled_date');
            $table->enum('status', ['Pending', 'Completed', 'Cancelled', 'Rescheduled'])->default('Pending');
            $table->text('notes')->nullable();
            $table->date('completed_date')->nullable();
            $table->text('review_findings')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_date']);
        });

        Schema::create('job_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('oku_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['Interested', 'Applied', 'Shortlisted', 'Interviewed', 'Hired', 'Rejected'])->default('Interested');
            $table->text('notes')->nullable();
            $table->date('application_date')->nullable();
            $table->date('interview_date')->nullable();
            $table->timestamps();
            $table->unique(['oku_id', 'job_id']);
            $table->index('status');
        });

        Schema::create('oku_category_matches', function (Blueprint $table) {
            $table->id();
            $table->string('oku_category', 50);
            $table->string('job_category', 100);
            $table->unsignedTinyInteger('match_score')->default(50);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['oku_category', 'job_category']);
        });
    }

    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        foreach (['oku_category_matches', 'job_interests', 'review_schedules', 'welfare_applications', 'oku_employments', 'jobs', 'employers', 'okus'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::enableForeignKeyConstraints();
    }
};
