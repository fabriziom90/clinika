<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Nationality;

class NationalitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $nationalities = [
            // Europa
            ['state' => 'Italia', 'name' => 'Italiana'],
            ['state' => 'Francia', 'name' => 'Francese'],
            ['state' => 'Germania', 'name' => 'Tedesca'],
            ['state' => 'Spagna', 'name' => 'Spagnola'],
            ['state' => 'Portogallo', 'name' => 'Portoghese'],
            ['state' => 'Regno Unito', 'name' => 'Britannica'],
            ['state' => 'Paesi Bassi', 'name' => 'Olandese'],
            ['state' => 'Belgio', 'name' => 'Belga'],
            ['state' => 'Svizzera', 'name' => 'Svizzera'],
            ['state' => 'Svezia', 'name' => 'Svedese'],
            ['state' => 'Norvegia', 'name' => 'Norvegese'],
            ['state' => 'Grecia', 'name' => 'Greca'],
            ['state' => 'Polonia', 'name' => 'Polacca'],

            // America
            ['state' => 'Stati Uniti', 'name' => 'Statunitense'],
            ['state' => 'Canada', 'name' => 'Canadese'],
            ['state' => 'Messico', 'name' => 'Messicana'],
            ['state' => 'Brasile', 'name' => 'Brasiliana'],
            ['state' => 'Argentina', 'name' => 'Argentina'],
            ['state' => 'Cile', 'name' => 'Chilena'],
            ['state' => 'Colombia', 'name' => 'Colombiana'],

            // Asia
            ['state' => 'Cina', 'name' => 'Cinese'],
            ['state' => 'Giappone', 'name' => 'Giapponese'],
            ['state' => 'Corea del Sud', 'name' => 'Sudcoreana'],
            ['state' => 'India', 'name' => 'Indiana'],
            ['state' => 'Indonesia', 'name' => 'Indonesiana'],
            ['state' => 'Filippine', 'name' => 'Filippina'],
            ['state' => 'Thailandia', 'name' => 'Thailandese'],
            ['state' => 'Vietnam', 'name' => 'Vietnamita'],
            ['state' => 'Arabia Saudita', 'name' => 'Saudita'],
            ['state' => 'Turchia', 'name' => 'Turca'],

            // Africa
            ['state' => 'Egitto', 'name' => 'Egiziana'],
            ['state' => 'Sudafrica', 'name' => 'Sudafricana'],
            ['state' => 'Nigeria', 'name' => 'Nigeriana'],
            ['state' => 'Kenya', 'name' => 'Kenyota'],
            ['state' => 'Marocco', 'name' => 'Marocchina'],
            ['state' => 'Etiopia', 'name' => 'Etiope'],
            ['state' => 'Ghana', 'name' => 'Ghanese'],

            // Oceania
            ['state' => 'Australia', 'name' => 'Australiana'],
            ['state' => 'Nuova Zelanda', 'name' => 'Neozelandese'],

            // Medio Oriente
            ['state' => 'Israele', 'name' => 'Israeliana'],
            ['state' => 'Iran', 'name' => 'Iraniana'],
            ['state' => 'Iraq', 'name' => 'Irachena'],
            ['state' => 'Emirati Arabi Uniti', 'name' => 'Emiratina'],
            ['state' => 'Qatar', 'name' => 'Qatariota']
        ];

        foreach($nationalities as $nationality){
            $newNationality = new Nationality();
            $newNationality->state = $nationality['state'];
            $newNationality->name = $nationality['name'];

            $newNationality->save();
        }
    }
}
