<?php

namespace Tests\Feature\Tenant;

use App\Models\Service;
use App\Models\Specialty;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SpecialtyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'tenant');

        Config::set('database.connections.tenant', [
            'driver' => 'mysql',
            'host' => '127.0.0.1',
            'port' => '3306',
            'database' => 'clinika_test_tenant',
            'username' => 'root',
            'password' => '',
            'unix_socket' => '',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');
    }

    public function test_specialty_is_created_in_tenant_database(): void
    {
        $specialty = Specialty::create([
            'name' => 'Cardiologia',
        ]);

        $this->assertSame('tenant', $specialty->getConnectionName());

        $this->assertDatabaseHas('specialties', [
            'id' => $specialty->id,
            'name' => 'Cardiologia',
        ], 'tenant');
    }

    public function test_specialty_hides_timestamps(): void
    {
        $specialty = Specialty::create([
            'name' => 'Cardiologia',
        ]);

        $data = $specialty->toArray();

        $this->assertArrayNotHasKey('created_at', $data);
        $this->assertArrayNotHasKey('updated_at', $data);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('name', $data);
    }

    public function test_specialty_has_services(): void
    {
        $specialty = Specialty::create([
            'name' => 'Cardiologia',
        ]);

        $service = Service::create([
            'name' => 'Visita cardiologica',
            'default_price' => 100.00,
            'default_duration' => 30,
            'active' => true,
        ]);

        $specialty->services()->attach($service->id);

        $specialty->load('services');

        $this->assertCount(1, $specialty->services);
        $this->assertSame($service->id, $specialty->services->first()->id);
        $this->assertSame('tenant', $specialty->services->first()->getConnectionName());
    }
}
