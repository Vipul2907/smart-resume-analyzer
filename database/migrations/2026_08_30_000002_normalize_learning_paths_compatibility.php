<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('learning_paths')) {
            return;
        }

        Schema::table('learning_paths', function (Blueprint $table): void {
            if (! Schema::hasColumn('learning_paths', 'title')) {
                $table->string('title')->nullable();
            }
            if (! Schema::hasColumn('learning_paths', 'target_role')) {
                $table->string('target_role')->nullable();
            }
            if (! Schema::hasColumn('learning_paths', 'summary')) {
                $table->text('summary')->nullable();
            }
            if (! Schema::hasColumn('learning_paths', 'source_snapshot')) {
                $table->json('source_snapshot')->nullable();
            }
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE learning_paths MODIFY status VARCHAR(30) NOT NULL DEFAULT 'active'");
            DB::table('learning_paths')->whereNull('title')->update(['title' => DB::raw('name')]);
        }
    }

    public function down(): void
    {
        // Keep existing user learning-path data safe.
    }
};
