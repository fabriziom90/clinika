<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Service extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = ['name', 'note', 'default_duration', 'default_price', 'active', 'code'];

    public function specialties()
    {
        return $this->belongsToMany(Specialty::class);
    }

    public function doctors()
    {
        $this->belongsToMany(Doctor::class)->withPivot(['price', 'duration_minutes', 'active']);
    }

    public function appointment()
    {
        $this->belongsTo(Appointment::class);
    }
}
