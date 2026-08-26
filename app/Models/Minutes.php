<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Minutes extends Model
{
    use HasFactory;
    protected $table = 'minutes';

    protected $fillable = [       
        'category',
        'series_number',
        'short_description',
        'barangay_name',   
        'presiding_officer',
        'venue',
        'date_created',       
        'editor_content',
        'is_deleted',          
        'is_archived',
        'added_by',    
        'attendance',
        'agenda_1',
        'agenda_2',
        'agenda_3',
        'agenda_4',
        'agenda_5',
    ];

    public function attendees()
    {
        return $this->hasMany(MinutesAttendance::class, 'minute_id');
    }
}
