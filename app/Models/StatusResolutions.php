<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusResolutions extends Model
{
    use HasFactory;
   protected $table = 'status_resolutions';

    protected $fillable = [       
        'status_code',
        'status_resolution',
        'is_deleted',    
        'order_level'   
    ];
}
