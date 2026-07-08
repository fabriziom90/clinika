<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientHealthHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'version',
        'user_id',
        'change_reason',
        'allergies',
        'chronic_diseases',
        'current_therapies',
        'surgical_history',
        'family_history',
        'lifestyle',
        'vaccinations',
        'notes',
        'is_current',
        'modified_at',
    ];

    protected $casts = [
        'change_reason' => 'encrypted',
        'allergies' => 'encrypted',
        'chronic_diseases' => 'encrypted',
        'current_therapies' => 'encrypted',
        'surgical_history' => 'encrypted',
        'family_history' => 'encrypted',
        'lifestyle' => 'encrypted',
        'vaccinations' => 'encrypted',
        'notes' => 'encrypted',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
