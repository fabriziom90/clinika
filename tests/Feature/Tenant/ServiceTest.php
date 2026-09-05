<?php

namespace Tests\Feature\Tenant;

use App\Models\Doctor;
use App\Models\InvoiceItem;
use App\Models\Service;
use App\Models\Specialty;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ServiceTest extends TestCase
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

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Visita specialistica',
            'default_price' => 100.00,
            'default_duration' => 30,
            'active' => true,
        ]);
    }

    private function createDoctor(): Doctor
    {
        return Doctor::create([
            'personal_code' => 'RSSMRA75A01H501X',
            'vat' => '12345678901',
            'birthday' => '1975-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Medica 1',
            'phone' => '3339876543',
            'genre' => 'M',
        ]);
    }

    public function test_service_is_created_in_tenant_database(): void
    {
        $service = $this->createService();

        $this->assertSame('tenant', $service->getConnectionName());

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Visita specialistica',
        ], 'tenant');
    }

    public function test_service_has_specialties(): void
    {
        $service = $this->createService();

        $specialty = Specialty::create([
            'name' => 'Cardiologia',
        ]);

        $service->specialties()->attach($specialty->id);

        $service->load('specialties');

        $this->assertCount(1, $service->specialties);
        $this->assertSame($specialty->id, $service->specialties->first()->id);
        $this->assertSame('tenant', $service->specialties->first()->getConnectionName());
    }

    public function test_service_has_doctors(): void
    {
        $service = $this->createService();
        $doctor = $this->createDoctor();

        $service->doctors()->attach($doctor->id, [
            'price' => 120.00,
            'duration_minutes' => 45,
            'active' => true,
        ]);

        $service->load('doctors');

        $this->assertCount(1, $service->doctors);
        $this->assertSame($doctor->id, $service->doctors->first()->id);
        $this->assertSame('tenant', $service->doctors->first()->getConnectionName());
    }

    public function test_service_doctors_relation_includes_pivot_data(): void
    {
        $service = $this->createService();
        $doctor = $this->createDoctor();

        $service->doctors()->attach($doctor->id, [
            'price' => 120.00,
            'duration_minutes' => 45,
            'active' => true,
        ]);

        $doctorFromRelation = $service->doctors()->first();

        $this->assertSame(120.00, (float) $doctorFromRelation->pivot->price);
        $this->assertSame(45, $doctorFromRelation->pivot->duration_minutes);
        $this->assertTrue((bool) $doctorFromRelation->pivot->active);
    }

    public function test_service_has_invoice_items(): void
    {
        $service = $this->createService();

        $invoiceItem = InvoiceItem::create([
            'invoice_id' => 1,
            'service_id' => $service->id,
            'description' => 'Visita specialistica',
            'quantity' => 1,
            'unit_price' => 100.00,
            'vat_percentage' => 0.00,
            'total' => 100.00,
        ]);

        $service->load('invoiceItems');

        $this->assertCount(1, $service->invoiceItems);
        $this->assertSame($invoiceItem->id, $service->invoiceItems->first()->id);
    }
}
