<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signatories extends Model
{    
    use HasFactory;
    protected $table = 'signatories';

    protected $fillable = [       
        'signatory_name',
        'position',
        'is_deleted',       
        'esignature'
    ];
}
