<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserPermissions extends Model
{
    
    use HasFactory;
    protected $table = 'account_type_permissions';

    protected $fillable = [       
        'account_type',
        'privilege',       
    ];
    
}
