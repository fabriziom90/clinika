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

    protected $fillable = [
        "appointment_id",
        "doctor_id",
        "patient_id",
        "number",
        "progressive_number",
        "year",
        "date",
        "subtotal",
        "vat_amount",
        "stamp_duty",
        "discount_amount",
        "total",
        "amount",
        "status",
        "full_name",
        "vat_number",
        "address",
        "city",
        "zip_code",
        "description",
        "user_id"
    ];

    protected $casts = [
        'issued_at' => 'date',
        'paid_at' => 'date',
    ];

    public function doctor(){
        return $this->belongsTo(Doctor::class);
    }

    public function patient(){
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
