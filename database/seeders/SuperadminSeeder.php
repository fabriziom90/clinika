<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class SuperadminSeeder extends Seeder
{
    public function run(): void
    {
        DB::connection('central')->table('users')->truncate();
        $email = 'superadmin@clinika.it';

        $user = new User;

        $user->setConnection('central');

        $user->name = 'Superadmin';
        $user->email = $email;
        $user->email_hash = hash('sha256', strtolower($email));
        $user->is_superadmin = true;
        $user->email_verified_at = now();
        $user->password = Hash::make('FmNdF07092024!!');

        $user->save();

        $this->command->info('Superadmin creato: '.$email);
    }
}
