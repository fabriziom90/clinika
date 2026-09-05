<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

abstract class TenantTestCase extends TestCase
{
    protected function createTenantDatabase(Clinic $clinic): void
    {
        $service = app(TenantDatabaseService::class);

        $service->createDatabase($clinic);
        $service->connect($clinic);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }
}
