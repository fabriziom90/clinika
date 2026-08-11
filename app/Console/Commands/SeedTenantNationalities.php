<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Console\Command;

class SeedTenantNationalities extends Command
{
    protected $signature = 'tenant:seed-nationalities {clinic_id}';

    protected $description = 'Esegue il NationalitySeeder nel database tenant della clinica';

    public function __construct(
        private TenantDatabaseService $tenantDatabaseService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $clinic = Clinic::on('central')->find($this->argument('clinic_id'));

        if (! $clinic) {
            $this->error('Clinica non trovata.');

            return self::FAILURE;
        }

        $this->tenantDatabaseService->connect($clinic);

        $this->call('db:seed', [
            '--class' => 'Database\\Seeders\\NationalitySeeder',
            '--database' => 'tenant',
        ]);

        $this->info("Nazionalità inserite nel tenant della clinica: {$clinic->name}");

        return self::SUCCESS;
    }
}
