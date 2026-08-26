<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommunicationsDocuments extends Model
{
    use HasFactory;
    protected $table = 'communications_documents';

    protected $fillable = [       
        'communication_id',
        'filename',    
        'is_deleted',      

    ];
}
