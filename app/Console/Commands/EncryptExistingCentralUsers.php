<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class EncryptExistingCentralUsers extends Command
{
    protected $signature = 'central:encrypt-existing-users';

    protected $description = 'Cifra i dati sensibili già presenti negli utenti del core';

    public function handle(): int
    {
        $connection = DB::connection('central');

        $this->info('Cifratura utenti core...');

        $connection->table('users')
            ->orderBy('id')
            ->each(function ($user) use ($connection) {
                $data = [];

                $email = $user->email;

                if ($email !== null && $email !== '') {
                    $data['email_hash'] = hash(
                        'sha256',
                        mb_strtolower(trim($email))
                    );

                    if (! $this->isEncrypted($email)) {
                        $data['email'] = Crypt::encryptString($email);
                    }
                }

                if (
                    $user->name !== null &&
                    $user->name !== '' &&
                    ! $this->isEncrypted($user->name)
                ) {
                    $data['name'] = Crypt::encryptString($user->name);
                }

                if (! empty($data)) {
                    $connection
                        ->table('users')
                        ->where('id', $user->id)
                        ->update($data);
                }
            });

        $this->info('Utenti core cifrati correttamente.');

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
