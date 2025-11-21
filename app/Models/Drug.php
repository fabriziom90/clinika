<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class Drug extends Pivot
{
    protected $fillable = ['name', 'unit_price'];
}
