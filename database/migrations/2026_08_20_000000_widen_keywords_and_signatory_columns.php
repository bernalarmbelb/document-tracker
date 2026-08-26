<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Widen columns that were varchar(45) and caused "Data too long" errors:
 *  - resolutions.keywords_tags  (real tags run 50-70+ chars)
 *  - signatories.signatory_name (combined names exceed 45)
 *
 * Uses raw ALTER statements so no doctrine/dbal dependency is required.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `resolutions` MODIFY `keywords_tags` VARCHAR(255) NULL");
        DB::statement("ALTER TABLE `signatories` MODIFY `signatory_name` VARCHAR(255) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `resolutions` MODIFY `keywords_tags` VARCHAR(45) NULL");
        DB::statement("ALTER TABLE `signatories` MODIFY `signatory_name` VARCHAR(45) NOT NULL");
    }
};
