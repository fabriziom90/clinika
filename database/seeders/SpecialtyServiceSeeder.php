<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\Specialty;
use Illuminate\Database\Seeder;

class SpecialtyServiceSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Cardiologia' => [
                [
                    'name' => 'Visita cardiologica',
                    'default_duration' => 30,
                    'default_price' => 100,
                ],
                [
                    'name' => 'Elettrocardiogramma (ECG)',
                    'default_duration' => 20,
                    'default_price' => 50,
                ],
                [
                    'name' => 'Ecocardiogramma',
                    'default_duration' => 40,
                    'default_price' => 120,
                ],
            ],
            'Dermatologia' => [
                [
                    'name' => 'Visita dermatologica',
                    'default_duration' => 30,
                    'default_price' => 80,
                ],
                [
                    'name' => 'Mappatura dei nei',
                    'default_duration' => 45,
                    'default_price' => 90,
                ],
            ],
            'Ortopedia' => [
                [
                    'name' => 'Visita ortopedica',
                    'default_duration' => 30,
                    'default_price' => 90,
                ],
                [
                    'name' => 'Infiltrazione articolare',
                    'default_duration' => 20,
                    'default_price' => 70,
                ],
            ],
        ];

        foreach ($data as $specialtyName => $services) {
            // crea o recupera la specializzazione
            $specialty = Specialty::firstOrCreate([
                'name' => $specialtyName,
            ]);

            foreach ($services as $serviceData) {
                // crea o recupera la prestazione
                $service = Service::firstOrCreate(
                    ['name' => $serviceData['name']],
                    [
                        'default_duration' => $serviceData['default_duration'],
                        'default_price' => $serviceData['default_price'],
                        'active' => true,
                        'code' => $this->generateCode($serviceData['name']),
                    ]
                );

                // collega prestazione ↔ specializzazione
                $specialty->services()->syncWithoutDetaching($service->id);
            }
        }
    }

    private function generateCode(string $name): string
    {
        $prefix = 'SRV';
        $code = $this->serviceCodeFromName($name);
        $date = now()->format('ym');

        $counter = Service::where('code', 'like', "{$prefix}-{$code}-{$date}%")->lockForUpdate()->count() + 1;

        $code = sprintf(
            '%s-%s-%s-%02d',
            $prefix,
            $code,
            $date,
            $counter
        );

        return $code;
    }

    public function serviceCodeFromName(string $name): string
    {
        return strtoupper(substr(preg_replace('/[^A-Za-z]/', '', $name), 0, 3));
    }
}
