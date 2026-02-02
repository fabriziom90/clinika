<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    protected $fillable = [
        'name',
        'surname',
        'personal_code',
        'birthday',
        'birth_city',
        'city',
        'address',
        'phone',
        'email',
        'nationality_id',
        'genre',
    ];

    protected $casts = [
        'name' => 'encrypted',
        'surname' => 'encrypted',
        'personal_code' => 'encrypted',
        'birth_city' => 'encrypted',
        'city' => 'encrypted',
        'address' => 'encrypted',
        'phone' => 'encrypted',
        'email' => 'encrypted',

    ];

    public function users()
    {
        return $this->belongsTo(User::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
