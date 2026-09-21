<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Attendance statuses changed from Present/Absent/Excused/Late (P/A/E/L)
 * to Present/Absent/Official Business/Leave (P/A/OB/LV) per client request.
 * Widens minutes_attendance.status from varchar(1) to fit the two-letter codes.
 * Uses a raw ALTER so no doctrine/dbal dependency is required.
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `minutes_attendance` MODIFY `status` VARCHAR(2) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `minutes_attendance` MODIFY `status` VARCHAR(1) NOT NULL");
    }
};
