<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private function dropIndexIfExists(string $table, string $index): void
    {
        $connection = DB::connection('tenant');

        $result = $connection->selectOne(
            'SELECT COUNT(*) AS `count`
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
             AND table_name = ?
             AND index_name = ?',
            [$table, $index]
        );

        if ((int) $result->count > 0) {
            $connection->statement(
                "ALTER TABLE `{$table}` DROP INDEX `{$index}`"
            );
        }
    }

    public function up(): void
    {
        $connection = DB::connection('tenant');

        /*
         * USERS
         */

        $this->dropIndexIfExists('users', 'users_email_unique');

        $connection->statement(
            'ALTER TABLE `users`
             MODIFY `name` TEXT NOT NULL,
             MODIFY `surname` TEXT NOT NULL,
             MODIFY `email` TEXT NOT NULL'
        );

        /*
         * DOCTORS
         */

        $this->dropIndexIfExists('doctors', 'doctors_pec_unique');

        $connection->statement(
            'ALTER TABLE `doctors`
             MODIFY `personal_code` TEXT NOT NULL,
             MODIFY `vat` TEXT NOT NULL,
             MODIFY `birthday` TEXT NOT NULL,
             MODIFY `birth_city` TEXT NULL,
             MODIFY `city` TEXT NOT NULL,
             MODIFY `address` TEXT NOT NULL,
             MODIFY `phone` TEXT NOT NULL,
             MODIFY `pec` TEXT NULL,
             MODIFY `genre` TEXT NOT NULL'
        );

        /*
         * NURSES
         */

        $this->dropIndexIfExists('nurses', 'nurses_pec_unique');

        $connection->statement(
            'ALTER TABLE `nurses`
             MODIFY `personal_code` TEXT NOT NULL,
             MODIFY `vat` TEXT NOT NULL,
             MODIFY `birthday` TEXT NOT NULL,
             MODIFY `birth_city` TEXT NULL,
             MODIFY `city` TEXT NOT NULL,
             MODIFY `address` TEXT NOT NULL,
             MODIFY `phone` TEXT NOT NULL,
             MODIFY `pec` TEXT NULL,
             MODIFY `genre` TEXT NOT NULL'
        );

        /*
         * PATIENTS
         */

        $connection->statement(
            'ALTER TABLE `patients`
             MODIFY `name` TEXT NOT NULL,
             MODIFY `surname` TEXT NOT NULL,
             MODIFY `personal_code` TEXT NOT NULL,
             MODIFY `birthday` TEXT NOT NULL,
             MODIFY `birth_city` TEXT NULL,
             MODIFY `city` TEXT NOT NULL,
             MODIFY `address` TEXT NOT NULL,
             MODIFY `phone` TEXT NOT NULL,
             MODIFY `email` TEXT NOT NULL,
             MODIFY `genre` TEXT NOT NULL,
             MODIFY `zip_code` TEXT NOT NULL'
        );
    }

    public function down(): void
    {
        $connection = DB::connection('tenant');

        /*
         * USERS
         */

        $connection->statement(
            'ALTER TABLE `users`
             MODIFY `name` VARCHAR(255) NOT NULL,
             MODIFY `surname` VARCHAR(255) NOT NULL,
             MODIFY `email` VARCHAR(70) NOT NULL'
        );

        $connection->statement(
            'ALTER TABLE `users`
             ADD UNIQUE INDEX `users_email_unique` (`email`)'
        );

        /*
         * DOCTORS
         */

        $connection->statement(
            'ALTER TABLE `doctors`
             MODIFY `personal_code` VARCHAR(255) NOT NULL,
             MODIFY `vat` VARCHAR(255) NOT NULL,
             MODIFY `birthday` VARCHAR(255) NOT NULL,
             MODIFY `birth_city` VARCHAR(255) NULL,
             MODIFY `city` VARCHAR(255) NOT NULL,
             MODIFY `address` VARCHAR(255) NOT NULL,
             MODIFY `phone` VARCHAR(255) NOT NULL,
             MODIFY `pec` VARCHAR(255) NULL,
             MODIFY `genre` VARCHAR(255) NOT NULL'
        );

        $connection->statement(
            'ALTER TABLE `doctors`
             ADD UNIQUE INDEX `doctors_pec_unique` (`pec`)'
        );

        /*
         * NURSES
         */

        $connection->statement(
            'ALTER TABLE `nurses`
             MODIFY `personal_code` VARCHAR(255) NOT NULL,
             MODIFY `vat` VARCHAR(255) NOT NULL,
             MODIFY `birthday` VARCHAR(255) NOT NULL,
             MODIFY `birth_city` VARCHAR(255) NULL,
             MODIFY `city` VARCHAR(255) NOT NULL,
             MODIFY `address` VARCHAR(255) NOT NULL,
             MODIFY `phone` VARCHAR(255) NOT NULL,
             MODIFY `pec` VARCHAR(255) NULL,
             MODIFY `genre` VARCHAR(255) NOT NULL'
        );

        $connection->statement(
            'ALTER TABLE `nurses`
             ADD UNIQUE INDEX `nurses_pec_unique` (`pec`)'
        );

        /*
         * PATIENTS
         */

        $connection->statement(
            'ALTER TABLE `patients`
             MODIFY `name` VARCHAR(255) NOT NULL,
             MODIFY `surname` VARCHAR(255) NOT NULL,
             MODIFY `personal_code` VARCHAR(255) NOT NULL,
             MODIFY `birthday` VARCHAR(255) NOT NULL,
             MODIFY `birth_city` VARCHAR(255) NULL,
             MODIFY `city` VARCHAR(255) NOT NULL,
             MODIFY `address` VARCHAR(255) NOT NULL,
             MODIFY `phone` VARCHAR(255) NOT NULL,
             MODIFY `email` VARCHAR(255) NOT NULL,
             MODIFY `genre` VARCHAR(255) NOT NULL,
             MODIFY `zip_code` VARCHAR(255) NOT NULL'
        );
    }
};
