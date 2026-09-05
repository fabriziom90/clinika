<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class TenantMiddlewareTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', env('DB_CONNECTION', 'mysql'));

        if (app()->bound('currentClinic')) {
            app()->forgetInstance('currentClinic');
        }

        Route::middleware('tenant')->get('/__test-tenant', function (Request $request) {
            $clinic = $request->attributes->get('clinic');
            $currentClinic = app()->bound('currentClinic')
                ? app('currentClinic')
                : null;

            return response()->json([
                'clinic_id' => $clinic?->id,
                'current_clinic_id' => $currentClinic?->id,
                'default_connection' => config('database.default'),
                'tenant_database' => config('database.connections.tenant.database'),
            ]);
        });
    }

    // identify a clinic, tenant connection extablished and currentClinic registered test
    public function test_it_identifies_an_active_clinic_and_configures_the_tenant_context(): void
    {
        $slug = 'middleware-'.fake()->uuid();

        $clinic = Clinic::factory()->create([
            'slug' => $slug,
            'active' => true,
            'database' => 'clinika_test_middleware',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
        ]);

        $response = $this->get("http://{$slug}.clinika.test/__test-tenant");

        $response->assertOk();

        $response->assertJson([
            'clinic_id' => $clinic->id,
            'current_clinic_id' => $clinic->id,
            'default_connection' => 'tenant',
            'tenant_database' => 'clinika_test_middleware',
        ]);
    }

    // central domain not identify a clinic test
    public function test_it_does_not_identify_a_clinic_for_the_central_domain(): void
    {
        $response = $this->get('http://clinika.test/__test-tenant');

        $response->assertOk();

        $response->assertJson([
            'clinic_id' => null,
            'current_clinic_id' => null,
        ]);

        $this->assertSame('mysql', config('database.default'));
    }

    // not existing subdomain not identify a clinic test
    public function test_it_does_not_identify_a_clinic_for_a_non_existing_subdomain(): void
    {
        $slug = 'inesistente-'.fake()->uuid();

        $response = $this->get("http://{$slug}.clinika.test/__test-tenant");

        $response->assertOk();

        $response->assertJson([
            'clinic_id' => null,
            'current_clinic_id' => null,
        ]);

        $this->assertSame('mysql', config('database.default'));
    }

    // clinic not active receive 403
    public function test_it_rejects_an_inactive_clinic(): void
    {
        $slug = 'inactive-'.fake()->uuid();

        Clinic::factory()->create([
            'slug' => $slug,
            'active' => false,
        ]);

        $response = $this->get("http://{$slug}.clinika.test/__test-tenant");

        $response->assertForbidden();
    }

    // clinic deleted receive 403 test
    public function test_it_rejects_a_soft_deleted_clinic(): void
    {
        $slug = 'deleted-'.fake()->uuid();

        $clinic = Clinic::factory()->create([
            'slug' => $slug,
            'active' => true,
        ]);

        $clinic->delete();

        $response = $this->get("http://{$slug}.clinika.test/__test-tenant");

        $response->assertForbidden();
    }
}
