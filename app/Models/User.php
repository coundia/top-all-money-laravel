<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles, HasFactory;

    protected $fillable = ['name','email','password'];
    protected $hidden = ['password','remember_token'];
    protected $casts = ['email_verified_at' => 'datetime','password' => 'hashed'];
    protected $guard_name = 'web';
}
