<?php

namespace Database\Factories;

use App\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ClinicFactory extends Factory
{
    protected $model = Clinic::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => fake()->company(),
            'slug' => fake()->unique()->slug(),
            'email' => fake()->safeEmail(),
            'phone' => '0612345678',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'province' => 'RM',
            'zip_code' => '00100',
            'vat_number' => 'IT12345678901',
            'tax_code' => 'RSSMRA80A01H501Z',
            'logo_path' => null,
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
            'active' => true,
        ];
    }
}
