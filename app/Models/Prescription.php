<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Prescription extends Model implements AuditableContract
{
    use Auditable;
    use HasFactory;

    protected $fillable = [
        'medical_entry_version_id',
        'drug_name',
        'dosage',
        'frequency',
        'duration',
        'notes',
    ];

    protected $casts = [
        'drug_name' => 'encrypted',
        'dosage' => 'encrypted',
        'frequency' => 'encrypted',
        'duration' => 'encrypted',
        'notes' => 'encrypted',
    ];

    public function version()
    {
        return $this->belongsTo(MedicalEntryVersion::class, 'medical_entry_version_id');
    }
}
