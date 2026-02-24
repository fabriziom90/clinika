<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_entry_version_id', 'path', 'original_name', 'mime', 'size',
    ];

    public function version()
    {
        return $this->belongsTo(MedicalEntryVersion::class, 'medical_entry_version_id');
    }
}
