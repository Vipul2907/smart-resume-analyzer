<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('private_documents')) return;

        Schema::create('private_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('original_filename');
            $table->string('file_path');
            $table->string('file_disk')->default('local');
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('file_size');
            $table->string('category', 30)->nullable();
            $table->text('extracted_text')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('private_documents');
    }
};
