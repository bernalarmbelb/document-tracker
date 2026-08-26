<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatusOrdinances extends Model
{
    use HasFactory;
    protected $table = 'status_ordinances';

    protected $fillable = [       
        'status_code',
        'status_ordinance',
        'is_deleted',    
        'order_level'   
    ];
}
