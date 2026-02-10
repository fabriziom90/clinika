<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

class Nurse extends Model implements AuditableContract
{
    use Auditable;

    protected $dontKeep = ['personal_code', 'vat', 'birthday', 'birth_city', 'city', 'address', 'phone', 'genre', 'pec'];

    protected $fillable = [
        'user_id',
        'personal_code',
        'vat',
        'birthday',
        'birth_city',
        'city',
        'address',
        'phone',
        'pec',
        'genre',
        'nationality_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function transformAudit(array $data): array
    {
        // Rimuove eventuali campi sensibili
        $sensitive = ['personal_code', 'vat', 'birthday', 'birth_city', 'city', 'address', 'phone', 'genre', 'pec'];

        foreach ($sensitive as $field) {
            unset($data['old_values'][$field], $data['new_values'][$field]);
        }

        return $data;
    }
}
