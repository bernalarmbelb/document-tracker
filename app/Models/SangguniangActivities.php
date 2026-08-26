<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SangguniangActivities extends Model
{
    use HasFactory;
   protected $table = 'sangguniang_activities';

    protected $fillable = [       
        'id',
        'activity_title',
        'description',    
        'type_of_activity',
        'activity_date',
        'duration',
        'location',
        'event_organizers',
        'sponsors',
        'guests_participants',
        'objective',
        'expected_attendees',
        'actual_attendees',
        'budget',
        'resolution',
        'remarks',
        'prepared_by',
        'status',
        'added_by',            
        'is_archived',        
        'is_deleted'
    ];
}
