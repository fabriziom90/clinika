<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Tests\TestCase;

class IdentifyTenantTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware('tenant')->get('/_test/tenant', function (Request $request) {
            return response()->json([
                'clinic_id' => $request->attributes->get('clinic')->id,
            ]);
        });
    }

    public function test_it_resolves_and_sets_the_current_clinic(): void
    {
        $clinic = Clinic::factory()->create([
            'slug' => 'ciccio-'.Str::uuid(),
            'active' => true,
        ]);

        $this->get("http://{$clinic->slug}.clinika.test/_test/tenant")
            ->assertOk()
            ->assertJson([
                'clinic_id' => $clinic->id,
            ]);
    }

    public function test_inactive_clinic_is_forbidden(): void
    {
        Clinic::factory()->create([
            'slug' => 'disattivata-'.Str::uuid(),
            'active' => false,
        ]);

        $this->get('http://disattivata.clinika.test/_test/tenant')
            ->assertForbidden();
    }

    public function test_deleted_clinic_is_forbidden(): void
    {
        $clinic = Clinic::factory()->create([
            'slug' => 'cancellata-'.Str::uuid(),
            'active' => true,
        ]);

        $clinic->delete();

        $this->get('http://cancellata.clinika.test/_test/tenant')
            ->assertForbidden();
    }
}
