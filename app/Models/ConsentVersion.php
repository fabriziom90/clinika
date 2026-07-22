<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsentVersion extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'consent_type_id', 'version', 'content', 'is_active', 'published_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'published_at' => 'datetime'
    ];

    public function consentType()
    {
        return $this->belongsTo(ConsentType::class);
    }
}
