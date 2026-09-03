<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('career_goals')) {
            return;
        }

        Schema::table('career_goals', function (Blueprint $table): void {
            if (! Schema::hasColumn('career_goals', 'target_salary')) {
                $table->unsignedInteger('target_salary')->nullable()->after('target_industry');
            }
            if (! Schema::hasColumn('career_goals', 'weekly_action')) {
                $table->string('weekly_action', 500)->nullable()->after('motivation');
            }
            if (! Schema::hasColumn('career_goals', 'career_advice')) {
                $table->json('career_advice')->nullable()->after('milestones');
            }
            if (! Schema::hasColumn('career_goals', 'career_advice_generated_at')) {
                $table->timestamp('career_advice_generated_at')->nullable()->after('career_advice');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('career_goals')) {
            return;
        }

        Schema::table('career_goals', function (Blueprint $table): void {
            $columns = collect(['target_salary', 'weekly_action', 'career_advice', 'career_advice_generated_at'])
                ->filter(fn (string $column) => Schema::hasColumn('career_goals', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
