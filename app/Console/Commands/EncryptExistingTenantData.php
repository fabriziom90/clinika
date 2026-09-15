<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Patient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptExistingTenantData extends Command
{
    protected $signature = 'tenant:encrypt-existing-data {clinic}';

    protected $description = 'Cifra i dati sensibili già presenti nel database tenant';

    public function handle(): int
    {
        $clinicSlug = $this->argument('clinic');

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->first();

        if (! $clinic) {
            $this->error("Clinica '{$clinicSlug}' non trovata.");

            return self::FAILURE;
        }

        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => $clinic->db_host,
            'port' => $clinic->db_port,
            'database' => $clinic->database,
            'username' => $clinic->db_username,
            'password' => $clinic->db_password,
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');

        $connection = DB::connection('tenant');

        try {
            $connection->getPdo();
        } catch (\Throwable $e) {
            $this->error('Impossibile collegarsi al database tenant.');
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info("Connessione alla clinica '{$clinicSlug}' effettuata.");
        $this->info('Cifratura dati tenant...');

        /*
         * USERS
         */

        $connection->table('users')
            ->orderBy('id')
            ->each(function ($user) use ($connection) {
                $data = [];

                $email = $user->email;

                if ($email !== null && $email !== '') {
                    $plainEmail = $email;

                    if ($this->isEncrypted($email)) {
                        $plainEmail = Crypt::decryptString($email);
                    }

                    $data['email_hash'] = hash(
                        'sha256',
                        mb_strtolower(trim($plainEmail))
                    );

                    if (! $this->isEncrypted($email)) {
                        $data['email'] = Crypt::encryptString($email);
                    }
                }

                foreach (['name', 'surname'] as $field) {
                    if (
                        $user->{$field} !== null &&
                        $user->{$field} !== '' &&
                        ! $this->isEncrypted($user->{$field})
                    ) {
                        $data[$field] = Crypt::encryptString($user->{$field});
                    }
                }

                if (! empty($data)) {
                    $connection
                        ->table('users')
                        ->where('id', $user->id)
                        ->update($data);
                }
            });

        $this->info('Users completati.');

        /*
         * DOCTORS
         */

        Doctor::on('tenant')
            ->orderBy('id')
            ->each(function (Doctor $doctor) use ($connection) {
                $fields = [
                    'personal_code',
                    'vat',
                    'birthday',
                    'birth_city',
                    'city',
                    'address',
                    'phone',
                    'pec',
                    'genre',
                ];

                $data = [];

                foreach ($fields as $field) {
                    if (
                        $doctor->{$field} !== null &&
                        $doctor->{$field} !== '' &&
                        ! $this->isEncrypted($doctor->{$field})
                    ) {
                        $data[$field] = Crypt::encryptString(
                            $doctor->{$field}
                        );
                    }
                }

                if (! empty($data)) {
                    $connection
                        ->table('doctors')
                        ->where('id', $doctor->id)
                        ->update($data);
                }
            });

        $this->info('Doctors completati.');

        /*
         * NURSES
         */

        Nurse::on('tenant')
            ->orderBy('id')
            ->each(function (Nurse $nurse) use ($connection) {
                $fields = [
                    'personal_code',
                    'vat',
                    'birthday',
                    'birth_city',
                    'city',
                    'address',
                    'phone',
                    'pec',
                    'genre',
                ];

                $data = [];

                foreach ($fields as $field) {
                    if (
                        $nurse->{$field} !== null &&
                        $nurse->{$field} !== '' &&
                        ! $this->isEncrypted($nurse->{$field})
                    ) {
                        $data[$field] = Crypt::encryptString(
                            $nurse->{$field}
                        );
                    }
                }

                if (! empty($data)) {
                    $connection
                        ->table('nurses')
                        ->where('id', $nurse->id)
                        ->update($data);
                }
            });

        $this->info('Nurses completati.');

        /*
         * PATIENTS
         */

        Patient::on('tenant')
            ->orderBy('id')
            ->each(function (Patient $patient) use ($connection) {
                $fields = [
                    'name',
                    'surname',
                    'personal_code',
                    'birthday',
                    'birth_city',
                    'city',
                    'address',
                    'phone',
                    'email',
                    'genre',
                    'zip_code',
                ];

                $data = [];

                foreach ($fields as $field) {
                    if (
                        $patient->{$field} !== null &&
                        $patient->{$field} !== '' &&
                        ! $this->isEncrypted($patient->{$field})
                    ) {
                        $data[$field] = Crypt::encryptString(
                            $patient->{$field}
                        );
                    }
                }

                if (! empty($data)) {
                    $connection
                        ->table('patients')
                        ->where('id', $patient->id)
                        ->update($data);
                }
            });

        $this->info('Patients completati.');
        $this->info('Cifratura completata.');

        return self::SUCCESS;
    }

    private function isEncrypted(?string $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        try {
            Crypt::decryptString($value);

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
