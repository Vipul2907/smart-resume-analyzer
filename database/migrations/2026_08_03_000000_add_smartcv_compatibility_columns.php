<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resumes', function (Blueprint $table): void {
            if (! Schema::hasColumn('resumes', 'name')) {
                $table->string('name')->nullable()->after('user_id');
            }

            if (! Schema::hasColumn('resumes', 'file_disk')) {
                $table->string('file_disk')->default('local')->after('file_path');
            }

            if (! Schema::hasColumn('resumes', 'extracted_text')) {
                $table->longText('extracted_text')->nullable()->after('file_size');
            }

            if (! Schema::hasColumn('resumes', 'parse_status')) {
                $table->string('parse_status')->default('pending')->after('extracted_text');
            }

            if (! Schema::hasColumn('resumes', 'is_primary')) {
                $table->boolean('is_primary')->default(false)->after('parse_status');
            }

            if (! Schema::hasColumn('resumes', 'last_analyzed_at')) {
                $table->timestamp('last_analyzed_at')->nullable()->after('is_primary');
            }
        });

        Schema::table('resume_versions', function (Blueprint $table): void {
            if (! Schema::hasColumn('resume_versions', 'label')) {
                $table->string('label')->nullable()->after('version_number');
            }

            if (! Schema::hasColumn('resume_versions', 'change_summary')) {
                $table->text('change_summary')->nullable()->after('label');
            }

            if (! Schema::hasColumn('resume_versions', 'is_current')) {
                $table->boolean('is_current')->default(false)->after('change_summary');
            }
        });
    }

    public function down(): void
    {
        // This migration supports an existing database. It intentionally does not remove shared columns.
    }
};
