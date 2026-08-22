<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('interview_sessions') || ! Schema::hasTable('skills')) {
            return;
        }

        // Some earlier SmartCV databases used enums. The current application needs
        // flexible values, so existing records are preserved while those columns are widened.
        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE interview_sessions MODIFY status VARCHAR(30) NOT NULL DEFAULT 'planned'");
            DB::statement("ALTER TABLE interview_sessions MODIFY type VARCHAR(30) NOT NULL DEFAULT 'general'");

            $proficiency = DB::selectOne("SHOW COLUMNS FROM skills WHERE Field = 'proficiency'");
            if ($proficiency && str_starts_with(strtolower($proficiency->Type), 'enum(')) {
                DB::statement("ALTER TABLE skills MODIFY proficiency VARCHAR(20) NOT NULL DEFAULT 'beginner'");
                DB::table('skills')->where('proficiency', 'beginner')->update(['proficiency' => '25']);
                DB::table('skills')->where('proficiency', 'intermediate')->update(['proficiency' => '50']);
                DB::table('skills')->where('proficiency', 'advanced')->update(['proficiency' => '75']);
                DB::table('skills')->where('proficiency', 'expert')->update(['proficiency' => '100']);
                DB::statement('ALTER TABLE skills MODIFY proficiency TINYINT UNSIGNED NULL');
            }

            DB::statement('ALTER TABLE skills MODIFY years_experience DECIMAL(4,1) NULL DEFAULT 0');
        }

        Schema::table('interview_sessions', function (Blueprint $table): void {
            if (! Schema::hasColumn('interview_sessions', 'target_role')) {
                $table->string('target_role')->nullable();
            }
            if (! Schema::hasColumn('interview_sessions', 'session_type')) {
                $table->string('session_type')->nullable();
            }
        });

        Schema::table('skills', function (Blueprint $table): void {
            if (! Schema::hasColumn('skills', 'is_priority')) {
                $table->boolean('is_priority')->default(false);
            }
            if (! Schema::hasColumn('skills', 'certificate_original_filename')) {
                $table->string('certificate_original_filename')->nullable();
            }
            if (! Schema::hasColumn('skills', 'certificate_path')) {
                $table->string('certificate_path')->nullable();
            }
            if (! Schema::hasColumn('skills', 'certificate_disk')) {
                $table->string('certificate_disk')->nullable();
            }
            if (! Schema::hasColumn('skills', 'certificate_mime_type')) {
                $table->string('certificate_mime_type', 100)->nullable();
            }
            if (! Schema::hasColumn('skills', 'certificate_size')) {
                $table->unsignedBigInteger('certificate_size')->nullable();
            }
        });
    }

    public function down(): void
    {
        // This is a compatibility migration. Keep user data and widened columns intact.
    }
};
