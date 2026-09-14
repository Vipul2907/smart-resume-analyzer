<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('portfolio_projects')) {
            return;
        }

        Schema::table('portfolio_projects', function (Blueprint $table): void {
            if (! Schema::hasColumn('portfolio_projects', 'repository_url')) {
                $table->string('repository_url')->nullable();
            }
            if (! Schema::hasColumn('portfolio_projects', 'image_path')) {
                $table->string('image_path')->nullable();
            }
            if (! Schema::hasColumn('portfolio_projects', 'image_disk')) {
                $table->string('image_disk')->nullable();
            }
            if (! Schema::hasColumn('portfolio_projects', 'image_original_filename')) {
                $table->string('image_original_filename')->nullable();
            }
            if (! Schema::hasColumn('portfolio_projects', 'image_mime_type')) {
                $table->string('image_mime_type', 100)->nullable();
            }
            if (! Schema::hasColumn('portfolio_projects', 'visibility')) {
                $table->string('visibility', 20)->default('private');
            }
            if (! Schema::hasColumn('portfolio_projects', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (! Schema::hasColumn('portfolio_projects', 'display_order')) {
                $table->unsignedSmallInteger('display_order')->default(0);
            }
        });
    }

    public function down(): void
    {
        // Compatibility columns deliberately remain if this migration is rolled back.
        // Removing them could make existing portfolio images or public links unavailable.
    }
};
