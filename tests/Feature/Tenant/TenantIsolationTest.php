<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\Patient;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    protected array $tenantDatabases = [];

    protected function createTenantDatabase(): Clinic
    {
        $database = 'clinika_test_'.Str::lower(Str::random(12));

        $clinic = Clinic::factory()->create([
            'database' => $database,
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_username' => env('DB_USERNAME', 'root'),
            'db_password' => env('DB_PASSWORD', ''),
        ]);

        $this->tenantDatabases[] = $database;

        $service = app(TenantDatabaseService::class);

        $service->createDatabase($clinic);
        $service->connect($clinic);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        return $clinic;
    }

    protected function connectToClinic(Clinic $clinic): void
    {
        app(TenantDatabaseService::class)->connect($clinic);
    }

    protected function tearDown(): void
    {
        foreach ($this->tenantDatabases as $database) {
            DB::connection('central')->statement(
                'DROP DATABASE IF EXISTS `'.str_replace('`', '``', $database).'`'
            );
        }

        parent::tearDown();
    }

    public function test_tenants_are_physically_isolated(): void
    {
        $clinicA = $this->createTenantDatabase();
        $patientA = Patient::factory()->create([
            'name' => 'Patient A',
            'surname' => 'Tenant A',
        ]);

        $clinicB = $this->createTenantDatabase();
        $patientB = Patient::factory()->create([
            'name' => 'Patient B',
            'surname' => 'Tenant B',
        ]);

        $this->connectToClinic($clinicA);

        $patientFromA = Patient::find($patientA->id);
        $patientBIdFromA = Patient::find($patientB->id);

        $this->assertNotNull($patientFromA);
        $this->assertSame('Patient A', $patientFromA->name);

        $this->assertNotNull($patientBIdFromA);
        $this->assertSame('Patient A', $patientBIdFromA->name);

        $patientFromA->update([
            'name' => 'Patient A Modified',
        ]);

        $this->connectToClinic($clinicB);

        $patientFromB = Patient::find($patientB->id);

        $this->assertNotNull($patientFromB);
        $this->assertSame('Patient B', $patientFromB->name);

        $patientFromB->update([
            'name' => 'Patient B Modified',
        ]);

        $this->connectToClinic($clinicA);

        $patientAAfterUpdate = Patient::find($patientA->id);

        $this->assertNotNull($patientAAfterUpdate);
        $this->assertSame('Patient A Modified', $patientAAfterUpdate->name);

        $this->connectToClinic($clinicB);

        $patientBAfterUpdate = Patient::find($patientB->id);

        $this->assertNotNull($patientBAfterUpdate);
        $this->assertSame('Patient B Modified', $patientBAfterUpdate->name);

        $patientBAfterUpdate->delete();

        $this->connectToClinic($clinicA);

        $this->assertNotNull(Patient::find($patientA->id));

        $this->connectToClinic($clinicB);

        $this->assertNull(Patient::find($patientB->id));
    }
}
