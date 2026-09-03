<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('career_profiles')) {
            Schema::table('career_profiles', function (Blueprint $table): void {
                if (! Schema::hasColumn('career_profiles', 'public_slug')) $table->string('public_slug')->nullable()->unique()->after('website_url');
                if (! Schema::hasColumn('career_profiles', 'portfolio_is_public')) $table->boolean('portfolio_is_public')->default(false)->after('visibility');
                if (! Schema::hasColumn('career_profiles', 'contact_email')) $table->string('contact_email')->nullable()->after('public_slug');
                if (! Schema::hasColumn('career_profiles', 'show_contact_email')) $table->boolean('show_contact_email')->default(false)->after('contact_email');
                if (! Schema::hasColumn('career_profiles', 'show_resume')) $table->boolean('show_resume')->default(false)->after('show_contact_email');
            });
        }

        if (Schema::hasTable('portfolio_projects')) {
            Schema::table('portfolio_projects', function (Blueprint $table): void {
                if (! Schema::hasColumn('portfolio_projects', 'slug')) $table->string('slug')->nullable()->after('title');
                if (! Schema::hasColumn('portfolio_projects', 'visibility')) $table->string('visibility', 20)->default('private')->after('status');
                if (! Schema::hasColumn('portfolio_projects', 'outcome')) $table->text('outcome')->nullable()->after('description');
                if (! Schema::hasColumn('portfolio_projects', 'case_study')) $table->longText('case_study')->nullable()->after('outcome');
                if (! Schema::hasColumn('portfolio_projects', 'image_disk')) $table->string('image_disk')->nullable();
                if (! Schema::hasColumn('portfolio_projects', 'image_original_filename')) $table->string('image_original_filename')->nullable()->after('image_disk');
                if (! Schema::hasColumn('portfolio_projects', 'image_mime_type')) $table->string('image_mime_type', 100)->nullable()->after('image_original_filename');
            });
        }
    }

    public function down(): void
    {
        // Keep public portfolio content safe when rolling back a shared project.
    }
};
