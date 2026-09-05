<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('job_searches')) return;
        Schema::create('job_searches', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('keywords', 500);
            $table->string('location')->nullable();
            $table->string('work_mode', 30)->nullable();
            $table->string('experience_level', 30)->nullable();
            $table->string('frequency', 20)->default('weekly');
            $table->boolean('is_alert_enabled')->default(false);
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamp('last_alerted_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'is_alert_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('job_searches');
    }
};
