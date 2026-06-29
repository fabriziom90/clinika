<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Invoice extends Model implements AuditableContract
{
    use Auditable;
    use SoftDeletes;

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    protected $fillable = [
        'uuid',
        'appointment_id',
        'doctor_id',
        'patient_id',
        'number',
        'progressive_number',
        'year',
        'date',
        'subtotal',
        'vat_amount',
        'stamp_duty',
        'discount_amount',
        'total',
        'amount',
        'status',
        'payment_method',
        'full_name',
        'vat_number',
        'address',
        'city',
        'zip_code',
        'description',
        'user_id',
        'pdf_path',
    ];

    protected $casts = [
        'date' => 'datetime',
        'full_name' => 'encrypted',
        'vat_number' => 'encrypted',
        'address' => 'encrypted',
        'city' => 'encrypted',
        'zip_code' => 'encrypted',
        'description' => 'encrypted',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItem::class);
    }
}
