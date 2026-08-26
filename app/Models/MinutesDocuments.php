<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MinutesDocuments extends Model
{
    use HasFactory;
    protected $table = 'minutes_documents';

    protected $fillable = [       
        'minute_id',
        'filename',   
        'is_deleted',           

    ];
}
