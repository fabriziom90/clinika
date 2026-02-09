<?php

namespace Database\Seeders;

use App\Models\Drug;
use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drugs = [
            ['name' => 'Paracetamolo', 'unit_price' => 2.50],
            ['name' => 'Ibuprofene', 'unit_price' => 3.00],
            ['name' => 'Amoxicillina', 'unit_price' => 5.20],
            ['name' => 'Aspirina', 'unit_price' => 1.80],
            ['name' => 'Omeprazolo', 'unit_price' => 4.00],
            ['name' => 'Diclofenac', 'unit_price' => 3.50],
            ['name' => 'Metformina', 'unit_price' => 6.00],
            ['name' => 'Lorazepam', 'unit_price' => 7.20],
            ['name' => 'Cetirizina', 'unit_price' => 2.80],
            ['name' => 'Prednisone', 'unit_price' => 5.50],
        ];

        foreach ($drugs as $drug) {
            Drug::create($drug);
        }
    }
}
