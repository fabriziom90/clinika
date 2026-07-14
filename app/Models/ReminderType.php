<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'active', 'sent_before_value', 'sent_before_unit'];

    public function preferences()
    {
        return $this->hasMany(ReminderTypePreference::class);
    }

    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'reminder_type_preferences');
    }
}
