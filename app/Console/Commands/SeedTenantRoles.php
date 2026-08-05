<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedTenantRoles extends Command
{
    protected $signature = 'tenant:seed-roles {clinic : ID o slug della clinica}';

    protected $description = 'Esegue il RoleSeeder esclusivamente sul database del tenant specificato';

    public function handle(TenantDatabaseService $tenantDatabaseService): int
    {
        $identifier = $this->argument('clinic');

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
        $this->info('Eseguo RoleSeeder...');

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\RoleSeeder',
            '--force' => true,
        ]);

        $this->info("RoleSeeder completato per {$clinic->name}.");

        return self::SUCCESS;
    }
}
