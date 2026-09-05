<?php

namespace Tests\Feature\Tenant;

use App\Http\Controllers\Admin\DrugController;
use App\Http\Requests\StoreDrugRequest;
use App\Http\Requests\UpdateDrugRequest;
use App\Models\Clinic;
use App\Models\Drug;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DrugControllerTest extends TestCase
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

        foreach ([
            'drug.view',
            'drug.create',
            'drug.update',
            'drug.delete',
        ] as $permission) {
            Permission::on('tenant')->firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createClinic(): Clinic
    {
        $clinic = Clinic::on('central')->create([
            'uuid' => Str::uuid(),
            'name' => 'Test Clinic',
            'slug' => 'test-'.Str::lower(Str::random(8)),
            'email' => 'test@example.com',
            'phone' => '3331234567',
            'address' => 'Via Roma 1',
            'city' => 'Roma',
            'province' => 'RM',
            'zip_code' => '00100',
            'database' => 'clinika_test_tenant',
            'db_host' => '127.0.0.1',
            'db_port' => '3306',
            'db_username' => 'root',
            'db_password' => '',
            'active' => true,
        ]);

        DB::purge('tenant');
        DB::reconnect('tenant');

        return $clinic;
    }

    private function createUser(): User
    {
        $user = User::factory()->make();
        $user->setConnection('tenant');
        $user->save();

        return $user;
    }

    private function createDrug(string $name = 'Paracetamolo', $unitPrice = '5.50'): Drug
    {
        $drug = new Drug;
        $drug->setConnection('tenant');
        $drug->name = $name;
        $drug->unit_price = $unitPrice;
        $drug->save();

        return $drug;
    }

    private function validDrugData(): array
    {
        return [
            'name' => 'Ibuprofene',
            'unit_price' => '8.50',
        ];
    }

    private function prepareRequest(FormRequest $request, array $data): void
    {
        $request->replace($data);
        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();
    }

    /*
    |--------------------------------------------------------------------------
    | Policy
    |--------------------------------------------------------------------------
    */

    public function test_policy_view_any_requires_permission(): void
    {
        $user = $this->createUser();

        $this->assertFalse(
            app(\App\Policies\DrugPolicy::class)->viewAny($user)
        );

        $user->givePermissionTo('drug.view');

        $this->assertTrue(
            app(\App\Policies\DrugPolicy::class)->viewAny($user)
        );
    }

    public function test_policy_view_requires_permission(): void
    {
        $user = $this->createUser();
        $drug = $this->createDrug();

        $policy = app(\App\Policies\DrugPolicy::class);

        $this->assertFalse($policy->view($user, $drug));

        $user->givePermissionTo('drug.view');

        $this->assertTrue($policy->view($user, $drug));
    }

    public function test_policy_create_requires_permission(): void
    {
        $user = $this->createUser();

        $policy = app(\App\Policies\DrugPolicy::class);

        $this->assertFalse($policy->create($user));

        $user->givePermissionTo('drug.create');

        $this->assertTrue($policy->create($user));
    }

    public function test_policy_update_requires_permission(): void
    {
        $user = $this->createUser();
        $drug = $this->createDrug();

        $policy = app(\App\Policies\DrugPolicy::class);

        $this->assertFalse($policy->update($user, $drug));

        $user->givePermissionTo('drug.update');

        $this->assertTrue($policy->update($user, $drug));
    }

    public function test_policy_delete_requires_permission(): void
    {
        $user = $this->createUser();
        $drug = $this->createDrug();

        $policy = app(\App\Policies\DrugPolicy::class);

        $this->assertFalse($policy->delete($user, $drug));

        $user->givePermissionTo('drug.delete');

        $this->assertTrue($policy->delete($user, $drug));
    }

    /*
    |--------------------------------------------------------------------------
    | StoreDrugRequest
    |--------------------------------------------------------------------------
    */

    public function test_store_request_requires_name(): void
    {
        $request = StoreDrugRequest::create('/admin/drugs', 'POST', [
            'unit_price' => '5.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_name_longer_than_120_characters(): void
    {
        $request = StoreDrugRequest::create('/admin/drugs', 'POST', [
            'name' => str_repeat('A', 121),
            'unit_price' => '5.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_store_request_accepts_nullable_unit_price(): void
    {
        $request = StoreDrugRequest::create('/admin/drugs', 'POST', [
            'name' => 'Paracetamolo',
            'unit_price' => null,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_store_request_rejects_non_numeric_unit_price(): void
    {
        $request = StoreDrugRequest::create('/admin/drugs', 'POST', [
            'name' => 'Paracetamolo',
            'unit_price' => 'abc',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_negative_unit_price(): void
    {
        $request = StoreDrugRequest::create('/admin/drugs', 'POST', [
            'name' => 'Paracetamolo',
            'unit_price' => '-5.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_more_than_two_decimal_places(): void
    {
        $request = StoreDrugRequest::create('/admin/drugs', 'POST', [
            'name' => 'Paracetamolo',
            'unit_price' => '5.555',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    /*
    |--------------------------------------------------------------------------
    | UpdateDrugRequest
    |--------------------------------------------------------------------------
    */

    public function test_update_request_requires_name(): void
    {
        $request = UpdateDrugRequest::create('/admin/drugs/1', 'PUT', [
            'unit_price' => '5.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_name_longer_than_120_characters(): void
    {
        $request = UpdateDrugRequest::create('/admin/drugs/1', 'PUT', [
            'name' => str_repeat('A', 121),
            'unit_price' => '5.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_update_request_accepts_nullable_unit_price(): void
    {
        $request = UpdateDrugRequest::create('/admin/drugs/1', 'PUT', [
            'name' => 'Paracetamolo',
            'unit_price' => null,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertFalse($validator->fails());
    }

    public function test_update_request_rejects_more_than_two_decimal_places(): void
    {
        $request = UpdateDrugRequest::create('/admin/drugs/1', 'PUT', [
            'name' => 'Paracetamolo',
            'unit_price' => '5.555',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    /*
    |--------------------------------------------------------------------------
    | Controller
    |--------------------------------------------------------------------------
    */

    public function test_index_returns_inertia_response(): void
    {
        $clinic = $this->createClinic();

        $drug = $this->createDrug();

        $user = $this->createUser();
        $user->givePermissionTo('drug.view');

        $response = $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/drugs");

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page
            ->component('Drugs/IndexDrugs')
            ->has('drugs')
            ->where('drugs', fn ($drugs) => $drugs->contains(
                fn ($item) => $item['id'] === $drug->id
                    && $item['name'] === 'Paracetamolo'
            ))
            ->where('columns', [
                'id' => 'ID',
                'name' => 'Nome',
                'unit_price' => 'Prezzo unitario',
            ])
        );
    }

    public function test_create_returns_null(): void
    {
        $this->createClinic();

        $response = app(DrugController::class)->create();

        $this->assertNull($response);
    }

    public function test_store_creates_drug(): void
    {
        $this->createClinic();

        $request = StoreDrugRequest::create('/admin/drugs', 'POST', $this->validDrugData());

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(DrugController::class)->store($request);

        $this->assertNotNull($response);

        $drug = Drug::on('tenant')
            ->where('name', 'Ibuprofene')
            ->first();

        $this->assertNotNull($drug);
        $this->assertSame('Ibuprofene', $drug->name);
        $this->assertEquals(8.50, $drug->unit_price);
    }

    public function test_show_returns_null(): void
    {
        $this->createClinic();

        $drug = $this->createDrug();

        $response = app(DrugController::class)->show($drug);

        $this->assertNull($response);
    }

    public function test_edit_returns_null(): void
    {
        $this->createClinic();

        $drug = $this->createDrug();

        $response = app(DrugController::class)->edit($drug);

        $this->assertNull($response);
    }

    public function test_update_updates_drug(): void
    {
        $this->createClinic();

        $drug = $this->createDrug();

        $request = UpdateDrugRequest::create('/admin/drugs/'.$drug->id, 'PUT', [
            'name' => 'Ibuprofene',
            'unit_price' => '12.75',
        ]);

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $controller = app(DrugController::class);
        $response = $controller->update($request, $drug);

        $this->assertNotNull($response);

        $drug->refresh();

        $this->assertSame('Ibuprofene', $drug->name);
        $this->assertSame('12.75', (string) $drug->unit_price);
    }

    public function test_destroy_deletes_drug(): void
    {
        $this->createClinic();

        $drug = $this->createDrug();

        $drugId = $drug->id;

        $response = app(DrugController::class)->destroy($drug);

        $this->assertNotNull($response);

        $this->assertDatabaseMissing('drugs', [
            'id' => $drugId,
        ], 'tenant');
    }
}
