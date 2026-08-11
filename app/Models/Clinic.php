<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Clinic extends Model
{
    use HasFactory, SoftDeletes;

    protected $connection = 'central';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'province',
        'zip_code',
        'vat_number',
        'tax_code',
        'logo_path',
        'database',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'active',
    ];

    protected $casts = [
        'email' => 'encrypted',
        'phone' => 'encrypted',
        'address' => 'encrypted',
        'city' => 'encrypted',
        'province' => 'encrypted',
        'zip_code' => 'encrypted',
        'vat_number' => 'encrypted',
        'tax_code' => 'encrypted',
        'db_username' => 'encrypted',
        'db_password' => 'encrypted',
        'active' => 'boolean',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withTimestamps();
    }

    protected static function booted()
    {
        static::creating(function (Clinic $clinic) {
            $clinic->uuid ??= (string) Str::uuid();
        });
    }
}
