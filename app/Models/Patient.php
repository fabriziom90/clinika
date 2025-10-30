<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
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
        'genre'
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function nationalities(){
        return $this->belongsTo(Nationality::class);
    }

}
