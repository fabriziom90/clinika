<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class InvoiceItem extends Model implements AuditableContract
{
    use Auditable;
    use SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'description',
        'quantity',
        'unit_price',
        'vat_percentage',
        'total',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function service(){
        return $this->belongsTo(Service::class);
    }

}
