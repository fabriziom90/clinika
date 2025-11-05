<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nurse extends Model
{
    use HasFactory;

     protected $fillable = [
        'user_id',
        'personal_code',
        'vat',
        'birthday',
        'birth_city',
        'city',
        'address',
        'phone',
        'pec',
        'genre',
        'nationality_id'
    ];

    public function users(){
        return $this->belongsTo(User::class);
    }

    public function nationalities(){
        return $this->belongsTo(Nationality::class);
    }

}
