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
        'name', 'email', 'email_hash', 'password', 'is_superadmin',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'name' => 'encrypted',
        'email' => 'encrypted',
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    protected static function booted(): void
    {
        static::saving(function (CentralUser $user) {
            if ($user->isDirty('email') && $user->email) {
                $user->email_hash = hash(
                    'sha256',
                    mb_strtolower(trim($user->email))
                );
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_superadmin' => 'boolean',
        ];
    }
}
