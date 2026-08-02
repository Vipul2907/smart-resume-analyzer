<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('career_profiles', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('headline')->nullable();
            $table->string('phone', 40)->nullable();
            $table->string('location')->nullable();
            $table->text('about')->nullable();
            $table->string('current_position')->nullable();
            $table->string('current_company')->nullable();
            $table->string('linkedin_url')->nullable();
            $table->string('github_url')->nullable();
            $table->string('website_url')->nullable();
            $table->string('visibility')->default('private');
            $table->boolean('available_for_work')->default(false);
            $table->unsignedTinyInteger('profile_completeness')->default(0);
            $table->timestamps();
        });

        Schema::create('resumes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_disk')->default('local');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->longText('extracted_text')->nullable();
            $table->string('parse_status')->default('pending');
            $table->boolean('is_primary')->default(false);
            $table->timestamp('last_analyzed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_primary']);
            $table->index(['user_id', 'parse_status']);
        });

        Schema::create('resume_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('resume_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('version_number');
            $table->string('label');
            $table->text('change_summary')->nullable();
            $table->json('content')->nullable();
            $table->boolean('is_current')->default(false);
            $table->timestamps();

            $table->unique(['resume_id', 'version_number']);
            $table->index(['resume_id', 'is_current']);
        });

        Schema::create('job_applications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->string('company');
            $table->string('role');
            $table->string('location')->nullable();
            $table->string('work_mode')->nullable();
            $table->string('job_url')->nullable();
            $table->string('source')->nullable();
            $table->string('status')->default('saved');
            $table->decimal('salary_min', 12, 2)->nullable();
            $table->decimal('salary_max', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->date('applied_at')->nullable();
            $table->date('follow_up_at')->nullable();
            $table->timestamp('next_interview_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'follow_up_at']);
        });

        Schema::create('interview_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('target_role')->nullable();
            $table->string('session_type')->default('general');
            $table->string('status')->default('planned');
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->json('questions')->nullable();
            $table->json('responses')->nullable();
            $table->json('feedback')->nullable();
            $table->unsignedTinyInteger('overall_score')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'scheduled_at']);
        });

        Schema::create('skills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->nullable();
            $table->unsignedTinyInteger('proficiency')->nullable();
            $table->decimal('years_experience', 4, 1)->nullable();
            $table->unsignedTinyInteger('target_proficiency')->nullable();
            $table->boolean('is_priority')->default(false);
            $table->text('evidence')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'name']);
            $table->index(['user_id', 'is_priority']);
        });

        Schema::create('career_goals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('target_role')->nullable();
            $table->string('target_industry')->nullable();
            $table->date('target_date')->nullable();
            $table->string('status')->default('active');
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('motivation')->nullable();
            $table->json('milestones')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('portfolio_projects', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->string('tagline')->nullable();
            $table->text('description')->nullable();
            $table->string('role')->nullable();
            $table->json('skills')->nullable();
            $table->string('project_url')->nullable();
            $table->string('repository_url')->nullable();
            $table->string('image_path')->nullable();
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status')->default('completed');
            $table->boolean('is_featured')->default(false);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'is_featured']);
        });

        Schema::create('ai_analyses', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->string('analysis_type');
            $table->string('provider')->nullable();
            $table->string('model')->nullable();
            $table->string('status')->default('pending');
            $table->string('prompt_version')->nullable();
            $table->json('input_snapshot')->nullable();
            $table->json('result')->nullable();
            $table->unsignedTinyInteger('score')->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'analysis_type', 'status']);
            $table->index(['resume_id', 'created_at']);
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('ai_analyses');
        Schema::dropIfExists('portfolio_projects');
        Schema::dropIfExists('career_goals');
        Schema::dropIfExists('skills');
        Schema::dropIfExists('interview_sessions');
        Schema::dropIfExists('job_applications');
        Schema::dropIfExists('resume_versions');
        Schema::dropIfExists('resumes');
        Schema::dropIfExists('career_profiles');
    }
};
