<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class InventoryDrug extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = ['room_id', 'drug_id', 'expiry_date', 'units'];

    public function room()
    {
        return $this->belongsTo(ClinicRoom::class);
    }

    public function drug()
    {
        return $this->belongsTo(Drug::class);
    }
}
