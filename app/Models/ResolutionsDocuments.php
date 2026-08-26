<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResolutionsDocuments extends Model
{
    use HasFactory;
    protected $table = 'resolutions_documents';

    protected $fillable = [       
        'resolution_id',
        'filename',    
        'is_deleted',          

    ];
}
