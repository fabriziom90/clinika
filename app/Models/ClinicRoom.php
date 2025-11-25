<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClinicRoom extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function inventoryProducts()
    {
        return $this->hasMany(InventoryProduct::class);
    }

    public function inventoryDrugs()
    {
        return $this->hasMany(InventoryDrug::class);
    }
}
