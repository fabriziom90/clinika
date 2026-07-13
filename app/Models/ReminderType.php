<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReminderType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'code', 'description', 'active', 'sent_before_value', 'sent_before_unit'];
}
