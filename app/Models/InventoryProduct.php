<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryProduct extends Model
{
    use HasFactory;

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
