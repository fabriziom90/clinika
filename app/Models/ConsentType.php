<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ConsentType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['code', 'name', 'description', 'acquisition_method', 'is_required', 'is_active'];

    protected $casts = [
        'is_required' => 'boolean',
        'is_active'     => 'boolean'
    ];
}
