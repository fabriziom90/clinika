<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class InventoryProduct extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = ['room_id', 'product_id', 'expiry_date', 'units'];

    public function room()
    {
        return $this->belongsTo(ClinicRoom::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
