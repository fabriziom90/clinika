<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Drug extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = ['name', 'unit_price'];

    public function inventoryDrugs()
    {
        return $this->hasMany(InventoryDrug::class);
    }
}
