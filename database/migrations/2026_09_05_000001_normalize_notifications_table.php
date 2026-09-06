<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('notifications')) return;

        Schema::table('notifications', function (Blueprint $table): void {
            if (! Schema::hasColumn('notifications', 'notifiable_type')) {
                $table->string('notifiable_type')->nullable();
            }
            if (! Schema::hasColumn('notifications', 'notifiable_id')) {
                $table->unsignedBigInteger('notifiable_id')->nullable();
            }
        });

        if (Schema::hasColumn('notifications', 'user_id')) {
            DB::table('notifications')->whereNull('notifiable_type')->update([
                'notifiable_type' => User::class,
                'notifiable_id' => DB::raw('user_id'),
            ]);
        }

        if (! Schema::hasIndex('notifications', 'notifications_notifiable_type_notifiable_id_index')) {
            Schema::table('notifications', function (Blueprint $table): void {
                $table->index(['notifiable_type', 'notifiable_id']);
            });
        }
    }

    public function down(): void
    {
        // Keep legacy and current notifications readable. Do not remove compatibility columns.
    }
};
