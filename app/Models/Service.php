<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'note', 'default_duration', 'default_price', 'active', 'code'];

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class);
    }

    public function doctors()
    {
        $this->belongsToMany(Doctor::class)->withPivot(['price', 'duration_minutes', 'active']);
    }

    public function appointment()
    {
        $this->belongsTo(Appointment::class);
    }
}
