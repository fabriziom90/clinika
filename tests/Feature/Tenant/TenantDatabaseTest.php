<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TenantDatabaseTest extends TestCase
{
    public function test_it_connects_to_the_clinic_database(): void
    {
        $clinic = Clinic::factory()->create([
            'database' => 'clinika_test_tenant',
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_username' => env('DB_USERNAME', 'root'),
            'db_password' => env('DB_PASSWORD', ''),
        ]);

        app(TenantDatabaseService::class)->connect($clinic);

        $this->assertSame('tenant', Config::get('database.default'));

        $this->assertSame(
            'clinika_test_tenant',
            Config::get('database.connections.tenant.database')
        );

        $this->assertSame(
            env('DB_HOST', '127.0.0.1'),
            Config::get('database.connections.tenant.host')
        );

        $this->assertSame(
            (string) env('DB_PORT', '3306'),
            (string) Config::get('database.connections.tenant.port')
        );

        $this->assertSame(
            'clinika_test_tenant',
            DB::connection('tenant')->getDatabaseName()
        );

        $this->assertSame(
            'tenant',
            DB::getDefaultConnection()
        );
    }
}
