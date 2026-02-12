<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicalAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_entry_id', 'path', 'original_name', 'mime', 'size',
    ];

    public function medicalEntry()
    {
        return $this->belongsTo(MedicalEntry::class);
    }
}
