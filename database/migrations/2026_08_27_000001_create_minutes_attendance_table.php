<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minutes_attendance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('minute_id')->index();
            $table->unsignedBigInteger('member_id')->index();
            $table->string('status', 1); // P, A, E, L
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minutes_attendance');
    }
};
