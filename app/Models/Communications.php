<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Communications extends Model
{
    use HasFactory;
    protected $table = 'communications';

    protected $fillable = [       
        'communication_type',
        'date_received',
        'date_released',
        'addressee',   
        'source',
        'particulars',
        'actions_taken',       
        'received_by',
        'released_by',
        'is_deleted',          
        'is_archived',
        'added_by',    
    ];
}
