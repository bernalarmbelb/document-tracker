<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinutesAttendance extends Model
{
    use HasFactory;
    protected $table = 'minutes_attendance';

    protected $fillable = [
        'minute_id',
        'member_id',
        'status',
    ];
}
