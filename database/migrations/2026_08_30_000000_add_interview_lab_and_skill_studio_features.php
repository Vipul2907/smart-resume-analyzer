<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('interview_sessions')) {
            Schema::table('interview_sessions', function (Blueprint $table): void {
                if (! Schema::hasColumn('interview_sessions', 'company_name')) {
                    $table->string('company_name')->nullable();
                }
                if (! Schema::hasColumn('interview_sessions', 'reminder_at')) {
                    $table->timestamp('reminder_at')->nullable();
                }
                if (! Schema::hasColumn('interview_sessions', 'recording_path')) {
                    $table->string('recording_path')->nullable();
                    $table->string('recording_disk')->nullable();
                    $table->string('recording_original_filename')->nullable();
                    $table->string('recording_mime_type', 100)->nullable();
                    $table->unsignedBigInteger('recording_size')->nullable();
                }
            });
        }

        if (! Schema::hasTable('skill_milestones')) {
            Schema::create('skill_milestones', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('skill_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->date('target_date')->nullable();
                $table->string('status')->default('planned');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['skill_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('skill_milestones');
        // Interview recordings belong to a user. Do not delete their metadata in a rollback.
    }
};
