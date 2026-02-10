<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Specialty extends Model implements AuditableContract
{
    use Auditable;

    protected $fillable = ['name'];

    protected $hidden = ['created_at', 'updated_at'];

    public function services()
    {
        return $this->belongsToMany(Service::class);
    }
}
