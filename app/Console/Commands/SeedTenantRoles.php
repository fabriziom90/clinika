<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTenantRoles extends Command
{
    protected $signature = 'tenant:seed-roles {clinic : ID o slug della clinica} {--db-name : Usa l\'argomento come nome del database tenant}';

    protected $description = 'Esegue il RoleSeeder esclusivamente sul database del tenant specificato';

    public function handle(TenantDatabaseService $tenantDatabaseService): int
    {
        $identifier = $this->argument('clinic');

        if ($this->option('db-name')) {
            config([
                'database.connections.tenant.host' => env('DB_HOST', '127.0.0.1'),
                'database.connections.tenant.port' => env('DB_PORT', '3306'),
                'database.connections.tenant.database' => $identifier,
                'database.connections.tenant.username' => env('DB_USERNAME'),
                'database.connections.tenant.password' => env('DB_PASSWORD'),
            ]);

            DB::purge('tenant');
            DB::reconnect('tenant');
            DB::setDefaultConnection('tenant');

            $this->info("Database: {$identifier}");
        } else {
            $clinic = Clinic::query()
                ->where('id', $identifier)
                ->orWhere('slug', $identifier)
                ->first();

            if (! $clinic) {
                $this->error("Clinica [{$identifier}] non trovata.");

                return self::FAILURE;
            }

            $this->info("Clinica: {$clinic->name}");
            $this->info("Database: {$clinic->database}");

            if (! $this->confirm('Eseguire RoleSeeder su questo database?')) {
                $this->info('Operazione annullata.');

                return self::SUCCESS;
            }

            $tenantDatabaseService->connect($clinic);

            DB::setDefaultConnection('tenant');

            $this->info('Connessione tenant configurata.');
        }

        if (! $this->option('db-name') && ! $this->confirm('Eseguire RoleSeeder su questo database?')) {
            $this->info('Operazione annullata.');

            return self::SUCCESS;
        }

        $this->info('Eseguo RoleSeeder...');

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\RoleSeeder',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $this->info('RoleSeeder completato.');

        return self::SUCCESS;
    }
}
