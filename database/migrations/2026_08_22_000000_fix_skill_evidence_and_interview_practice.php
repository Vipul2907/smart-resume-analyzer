<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('skills') && DB::connection()->getDriverName() === 'mysql') {
            // An earlier database used a JSON constraint for evidence. Skill evidence is plain text.
            DB::statement('ALTER TABLE skills MODIFY evidence LONGTEXT NULL');
        }

        if (Schema::hasTable('interview_sessions')) {
            Schema::table('interview_sessions', function (Blueprint $table): void {
                if (! Schema::hasColumn('interview_sessions', 'questions')) {
                    $table->json('questions')->nullable();
                }
                if (! Schema::hasColumn('interview_sessions', 'responses')) {
                    $table->json('responses')->nullable();
                }
                if (! Schema::hasColumn('interview_sessions', 'questions_count')) {
                    $table->unsignedInteger('questions_count')->default(0);
                }
                if (! Schema::hasColumn('interview_sessions', 'completed_questions')) {
                    $table->unsignedInteger('completed_questions')->default(0);
                }
            });
        }
    }

    public function down(): void
    {
        // Keep saved user evidence and interview answers safe.
    }
};
