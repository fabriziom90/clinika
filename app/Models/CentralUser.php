<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class CentralUser extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $connection = 'central';

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'is_superadmin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
        ];
    }
}
