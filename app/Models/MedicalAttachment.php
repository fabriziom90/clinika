<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class MedicalAttachment extends Model implements AuditableContract
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'medical_entry_version_id', 'path', 'original_name', 'mime', 'size',
    ];

    public function version()
    {
        return $this->belongsTo(MedicalEntryVersion::class, 'medical_entry_version_id');
    }
}
