<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class VitalParameter extends Model implements AuditableContract
{

    use Auditable;

    protected $fillable = [
        'medical_entry_version_id', 'pressure', 'heart_rate',
        'temperature', 'weight', 'height',
    ];

    public function version()
    {
        return $this->belongsTo(MedicalEntryVersion::class, 'medical_entry_version_id');
    }
}
