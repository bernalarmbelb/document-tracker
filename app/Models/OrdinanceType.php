<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdinanceType extends Model
{
    use HasFactory;
    protected $table = 'ordinance_type';

    protected $fillable = [       
        'ordinance_type',    
        'description',
        'is_deleted',       
    ];
}
