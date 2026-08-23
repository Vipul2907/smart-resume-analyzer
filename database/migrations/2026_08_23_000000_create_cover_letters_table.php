<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cover_letters', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_application_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('resume_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->string('template')->default('modern');
            $table->string('recipient_name')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            $table->string('subject')->nullable();
            $table->text('opening')->nullable();
            $table->longText('body')->nullable();
            $table->text('closing')->nullable();
            $table->string('signature_name')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('last_exported_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cover_letters');
    }
};
