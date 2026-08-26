<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdinancesDocuments extends Model
{
    use HasFactory;
    protected $table = 'ordinances_documents';

    protected $fillable = [       
        'ordinance_id',
        'filename',     
        'is_deleted',         

    ];
}
