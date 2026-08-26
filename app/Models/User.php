<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Models\UserPermissions;
use App\Models\OrdinanceType;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'account_type',        
        'fullname',       
        'status',
        'added_by',
        'is_deleted',
        'is_archived'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    

    public function hasPermission($privilege)
    {
        return UserPermissions::where('account_type', $this->account_type)
                             ->where('privilege', $privilege)
                             ->exists();
    }

    public function getOrdinanceTypes()
    {
        return OrdinanceType::where('is_deleted', 0)->get();
    }
}
