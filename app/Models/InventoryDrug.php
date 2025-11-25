<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDrug extends Model
{
    use HasFactory;

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
