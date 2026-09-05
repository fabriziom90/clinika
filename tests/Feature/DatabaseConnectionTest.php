<?php

namespace Tests\Feature;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_central_database_is_available(): void
    {
        $result = DB::connection('central')->select('SELECT 1');

        $this->assertNotEmpty($result);
    }

    public function test_tenant_database_is_available(): void
    {
        $clinic = Clinic::factory()->create([
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
        ]);

        $service = app(TenantDatabaseService::class);

        $service->createDatabase($clinic);
        $service->connect($clinic);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        $result = DB::connection('tenant')->select('SELECT 1');

        $this->assertNotEmpty($result);
    }

    public function test_central_and_tenant_are_different_databases(): void
    {
        $clinic = Clinic::factory()->create([
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
        ]);

        $service = app(TenantDatabaseService::class);

        $service->createDatabase($clinic);
        $service->connect($clinic);

        $centralDatabase = DB::connection('central')->getDatabaseName();
        $tenantDatabase = DB::connection('tenant')->getDatabaseName();

        $this->assertNotSame($centralDatabase, $tenantDatabase);
    }
}
