<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLogs extends Model
{
    use HasFactory;
    protected $table = 'activity_logs';

    protected $fillable = [       
        'user_id',
        'action',
        'description',
        'ip_address',  
        'activity_date',
        'user_agent'   
    ];
}
