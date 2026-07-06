<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Secretary extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'personal_code',
        'birthday',
        'birth_city',
        'nationality_id',
        'city',
        'address',
        'phone',
        'genre',
        'employee_code',
        'notes',
        'zip_code',
    ];

    protected $casts = [
        'personal_code' => 'encrypted',
        'birthday' => 'encrypted',
        'birth_city' => 'encrypted',
        'city' => 'encrypted',
        'address' => 'encrypted',
        'phone' => 'encrypted',
        'genre' => 'encrypted',
        'employee_code' => 'encrypted',
        'notes' => 'encrypted',
        'zip_code' => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function nationality()
    {
        return $this->belongsTo(Nationality::class);
    }

    public function transformAudit(array $data): array
    {
        // Rimuove eventuali campi sensibili
        $sensitive = ['personal_code', 'genre', 'birthday', 'address', 'phone', 'birth_city', 'city'];

        foreach ($sensitive as $field) {
            unset($data['old_values'][$field], $data['new_values'][$field]);
        }

        return $data;
    }
}
