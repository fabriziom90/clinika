<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MedicalEntryVersion extends Model implements AuditableContract
{
    use Auditable;
    use HasFactory;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'medical_entry_id',
        'version',
        'type',
        'title',
        'content',
        'created_by',
        'is_voided',
        'void_reason',
        'voided_by',
        'voided_at',
        'pdf_path',
        'uuid',
    ];

    protected $casts = [
        'content' => 'encrypted',
        'title' => 'encrypted',
    ];

    // RELAZIONE CON L'ENTRY PRINCIPALE
    public function medicalEntry()
    {
        return $this->belongsTo(MedicalEntry::class, 'medical_entry_id');
    }

    // PRESCRIPTIONS LEGATE A QUESTA VERSIONE
    public function prescriptions()
    {
        return $this->hasMany(Prescription::class, 'medical_entry_version_id');
    }

    // PARAMETRI VITALI LEGATI A QUESTA VERSIONE
    public function vitalParameters()
    {
        return $this->hasOne(VitalParameter::class, 'medical_entry_version_id');
    }

    // ALLEGATI LEGATI A QUESTA VERSIONE
    public function attachments()
    {
        return $this->hasMany(MedicalAttachment::class, 'medical_entry_version_id');
    }
}
