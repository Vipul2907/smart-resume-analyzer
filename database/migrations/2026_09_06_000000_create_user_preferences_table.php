<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_preferences')) {
            return;
        }

        Schema::create('user_preferences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->boolean('email_reminders')->default(true);
            $table->boolean('weekly_career_review')->default(false);
            $table->boolean('in_app_reminders')->default(true);
            $table->boolean('ai_processing_enabled')->default(true);
            $table->boolean('retain_ai_history')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
