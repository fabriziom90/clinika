<?php

namespace App\Console\Commands;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MigrateTenant extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migrate:tenant {clinic_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Esegue le migration sul database di una clinica';

    /**
     * Execute the console command.
     */
    public function handle(TenantDatabaseService $service): int
    {
        $clinic = Clinic::find($this->argument('clinic_id'));

        if (! $clinic) {
            $this->error('Clinica non trovata');

            return self::FAILURE;
        }

        $service->connect($clinic);

        $this->info(
            'Tenant DB: '.config('database.connections.tenant.database')
        );

        $this->info(
            'Actual DB: '.DB::connection('tenant')->getDatabaseName()
        );

        $this->call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        return self::SUCCESS;
    }
}
