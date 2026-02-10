<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class ClinicRoom extends Model implements AuditableContract
{
    use Auditable;

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
