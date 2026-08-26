<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SangguniangDocuments extends Model
{
    use HasFactory;
    protected $table = 'sangguniang_documents';

    protected $fillable = [       
        'activity_id',
        'filename',    
        'is_deleted',          

    ];
}
