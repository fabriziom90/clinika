<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DoctorCompensation extends Model
{
    use HasFactory;

    protected $fillable = [
        'doctor_id',
        'service_id',
        'invoice_id',
        'invoice_item_id',
        'compensation_type',
        'compensation_value',
        'service_amount',
        'compensation_amount',
        'status',
        'accrued_at',
        'paid_at',
    ];

    protected $casts = [
        'compensation_value' => 'decimal:2',
        'service_amount' => 'decimal:2',
        'compensation_amount' => 'decimal:2',
        'accrued_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function invoiceItem()
    {
        return $this->belongsTo(InvoiceItem::class);
    }
}
