<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('job_applications') || DB::getDriverName() !== 'mysql') {
            return;
        }

        // Older SmartCV databases used a MySQL ENUM for this field. An ENUM
        // rejects new workflow states, so use a short string instead.
        DB::statement("ALTER TABLE `job_applications` MODIFY `status` VARCHAR(30) NOT NULL DEFAULT 'saved'");
    }

    public function down(): void
    {
        // Do not narrow this field again: existing closed or withdrawn records
        // would be lost or become invalid.
    }
};
