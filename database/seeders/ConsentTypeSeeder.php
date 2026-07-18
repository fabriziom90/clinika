<?php

namespace Database\Seeders;

use App\Models\ConsentType;
use Illuminate\Database\Seeder;

class ConsentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $consents = [

            [
                'code' => 'privacy_gdpr',
                'name' => 'Consenso Privacy GDPR',
                'description' => 'Consenso al trattamento dei dati personali ai sensi del Regolamento UE 2016/679 (GDPR).',
                'acquisition_method' => 'paper',
                'is_required' => true,
                'is_active' => true,
            ],

            [
                'code' => 'health_data',
                'name' => 'Trattamento dati sanitari',
                'description' => 'Consenso al trattamento dei dati relativi alla salute per finalità di diagnosi, cura e assistenza.',
                'acquisition_method' => 'paper',
                'is_required' => true,
                'is_active' => true,
            ],

            [
                'code' => 'marketing',
                'name' => 'Consenso Marketing',
                'description' => 'Consenso all\'invio di comunicazioni promozionali e informative.',
                'acquisition_method' => 'paper',
                'is_required' => false,
                'is_active' => true,
            ],

            [
                'code' => 'newsletter',
                'name' => 'Iscrizione Newsletter',
                'description' => 'Consenso alla ricezione della newsletter della struttura.',
                'acquisition_method' => 'paper',
                'is_required' => false,
                'is_active' => true,
            ],

        ];

        foreach ($consents as $consent) {
            ConsentType::firstOrCreate(
                ['code' => $consent['code']],
                $consent
            );
        }
    }
}
