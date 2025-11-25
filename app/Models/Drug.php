<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Drug extends Model
{
    protected $fillable = ['name', 'unit_price'];


    public function inventoryDrugs()
    {
        return $this->hasMany(InventoryDrug::class);
    }
}
