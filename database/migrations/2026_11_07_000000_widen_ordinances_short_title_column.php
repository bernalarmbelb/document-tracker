<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Makes ordinances.short_title unbounded (TEXT) like resolutions.title
 * and minutes.short_description, which were already TEXT.
 *
 * Uses raw ALTER statements so no doctrine/dbal dependency is required.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `ordinances` MODIFY `short_title` TEXT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `ordinances` MODIFY `short_title` VARCHAR(255) NULL");
    }
};
