<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('skills') && ! Schema::hasColumn('skills', 'target_proficiency')) {
            Schema::table('skills', function (Blueprint $table): void {
                $table->unsignedTinyInteger('target_proficiency')->nullable()->after('proficiency');
            });
        }
    }

    public function down(): void
    {
        // Keep a user's saved proficiency target during a rollback.
    }
};
