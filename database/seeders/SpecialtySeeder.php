<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Specialty;

class SpecialtySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $medical_specializations = [
            'Allergologia e Immunologia',
            'Anestesia e Rianimazione',
            'Cardiologia',
            'Chirurgia Generale',
            'Chirurgia Plastica e Ricostruttiva',
            'Chirurgia Vascolare',
            'Chirurgia Toracica',
            'Chirurgia Maxillo-Facciale',
            'Dermatologia e Venereologia',
            'Endocrinologia e Malattie del Metabolismo',
            'Gastroenterologia',
            'Geriatria',
            'Ginecologia e Ostetricia',
            'Ematologia',
            'Malattie Infettive',
            'Medicina del Lavoro',
            'Medicina di Emergenza e Urgenza',
            'Medicina Fisica e Riabilitativa',
            'Medicina Generale',
            'Medicina Interna',
            'Medicina Legale',
            'Nefrologia',
            'Neonatologia',
            'Neurochirurgia',
            'Neurologia',
            'Oculistica (Oftalmologia)',
            'Oncologia',
            'Ortopedia e Traumatologia',
            'Otorinolaringoiatria',
            'Pediatria',
            'Pneumologia',
            'Psichiatria',
            'Radiologia',
            'Reumatologia',
            'Urologia'
        ];

        foreach($medical_specializations as $ms){
            $specialization = new Specialty();
            $specialization->name = $ms;

            $specialization->save();
        }
    }
}
