<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('job_applications', function (Blueprint $table): void {
            if (! Schema::hasColumn('job_applications', 'follow_up_at')) {
                $table->date('follow_up_at')->nullable();
            }
            if (! Schema::hasColumn('job_applications', 'priority')) {
                $table->unsignedTinyInteger('priority')->default(0);
            }
        });

        if (! Schema::hasTable('job_contacts')) {
            Schema::create('job_contacts', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('role')->nullable();
                $table->string('email')->nullable();
                $table->string('linkedin_url')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('job_attachments')) {
            Schema::create('job_attachments', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('job_application_id')->constrained()->cascadeOnDelete();
                $table->string('name');
                $table->string('original_filename');
                $table->string('file_path');
                $table->string('file_disk')->default('local');
                $table->string('mime_type', 100);
                $table->unsignedBigInteger('file_size');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('job_attachments');
        Schema::dropIfExists('job_contacts');
    }
};
