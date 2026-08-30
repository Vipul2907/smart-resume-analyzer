<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('learning_paths')) {
            Schema::create('learning_paths', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->string('target_role')->nullable();
                $table->text('summary')->nullable();
                $table->string('status')->default('active');
                $table->json('source_snapshot')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'status']);
            });
        }

        if (! Schema::hasTable('learning_path_items')) {
            Schema::create('learning_path_items', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('learning_path_id')->constrained()->cascadeOnDelete();
                $table->string('skill_name');
                $table->string('title');
                $table->text('description')->nullable();
                $table->string('resource_url')->nullable();
                $table->unsignedSmallInteger('estimated_hours')->nullable();
                $table->unsignedSmallInteger('position')->default(0);
                $table->string('status')->default('planned');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->index(['learning_path_id', 'status']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_path_items');
        Schema::dropIfExists('learning_paths');
    }
};
