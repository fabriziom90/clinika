<?php

namespace Tests\Feature\Tenant;

use App\Http\Controllers\Admin\InventoryDrugController;
use App\Http\Requests\StoreInventoryDrugRequest;
use App\Http\Requests\UpdateInventoryDrugRequest;
use App\Models\Clinic;
use App\Models\ClinicRoom;
use App\Models\Drug;
use App\Models\InventoryDrug;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryDrugControllerTest extends TestCase
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
            'inventory-drug.view',
            'inventory-drug.create',
            'inventory-drug.update',
            'inventory-drug.delete',
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

    private function createClinicRoom(string $name = 'Stanza 1'): ClinicRoom
    {
        $clinicRoom = new ClinicRoom;
        $clinicRoom->setConnection('tenant');
        $clinicRoom->name = $name;
        $clinicRoom->save();

        return $clinicRoom;
    }

    private function createDrug(string $name = 'Paracetamolo'): Drug
    {
        $drug = new Drug;
        $drug->setConnection('tenant');
        $drug->name = $name;
        $drug->unit_price = '5.50';
        $drug->save();

        return $drug;
    }

    private function createInventoryDrug(ClinicRoom $clinicRoom, Drug $drug, string $expiryDate = '2027-12-31', int $units = 10): InventoryDrug
    {
        $inventoryDrug = new InventoryDrug;
        $inventoryDrug->setConnection('tenant');
        $inventoryDrug->room_id = $clinicRoom->id;
        $inventoryDrug->drug_id = $drug->id;
        $inventoryDrug->expiry_date = $expiryDate;
        $inventoryDrug->units = $units;
        $inventoryDrug->save();

        return $inventoryDrug;
    }

    private function validInventoryDrugData(ClinicRoom $clinicRoom, Drug $drug): array
    {
        return [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => 10,
        ];
    }

    public function test_policy_view_any_requires_permission(): void
    {
        $user = $this->createUser();

        $this->assertFalse(
            app(\App\Policies\InventoryDrugPolicy::class)->viewAny($user)
        );

        $user->givePermissionTo('inventory-drug.view');

        $this->assertTrue(
            app(\App\Policies\InventoryDrugPolicy::class)->viewAny($user)
        );
    }

    public function test_policy_view_requires_permission(): void
    {
        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $policy = app(\App\Policies\InventoryDrugPolicy::class);

        $this->assertFalse($policy->view($user, $inventoryDrug));

        $user->givePermissionTo('inventory-drug.view');

        $this->assertTrue($policy->view($user, $inventoryDrug));
    }

    public function test_policy_create_requires_permission(): void
    {
        $user = $this->createUser();

        $policy = app(\App\Policies\InventoryDrugPolicy::class);

        $this->assertFalse($policy->create($user));

        $user->givePermissionTo('inventory-drug.create');

        $this->assertTrue($policy->create($user));
    }

    public function test_policy_update_requires_permission(): void
    {
        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $policy = app(\App\Policies\InventoryDrugPolicy::class);

        $this->assertFalse($policy->update($user, $inventoryDrug));

        $user->givePermissionTo('inventory-drug.update');

        $this->assertTrue($policy->update($user, $inventoryDrug));
    }

    public function test_policy_delete_requires_permission(): void
    {
        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $policy = app(\App\Policies\InventoryDrugPolicy::class);

        $this->assertFalse($policy->delete($user, $inventoryDrug));

        $user->givePermissionTo('inventory-drug.delete');

        $this->assertTrue($policy->delete($user, $inventoryDrug));
    }

    public function test_store_request_requires_expiry_date(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expiry_date', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_invalid_expiry_date(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => 'invalid-date',
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expiry_date', $validator->errors()->toArray());
    }

    public function test_store_request_requires_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_non_integer_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => 'abc',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_zero_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => 0,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_negative_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => -1,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_invalid_room_id(): void
    {
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => 999999,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('room_id', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_invalid_drug_id(): void
    {
        $clinicRoom = $this->createClinicRoom();

        $request = StoreInventoryDrugRequest::create('/admin/inventory-drugs', 'POST', [
            'room_id' => $clinicRoom->id,
            'drug_id' => 999999,
            'expiry_date' => '2027-12-31',
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('drug_id', $validator->errors()->toArray());
    }

    public function test_update_request_requires_expiry_date(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = UpdateInventoryDrugRequest::create('/admin/inventory-drugs/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expiry_date', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_invalid_expiry_date(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = UpdateInventoryDrugRequest::create('/admin/inventory-drugs/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => 'invalid-date',
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('expiry_date', $validator->errors()->toArray());
    }

    public function test_update_request_requires_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = UpdateInventoryDrugRequest::create('/admin/inventory-drugs/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_non_integer_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = UpdateInventoryDrugRequest::create('/admin/inventory-drugs/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => 'abc',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_zero_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = UpdateInventoryDrugRequest::create('/admin/inventory-drugs/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'drug_id' => $drug->id,
            'expiry_date' => '2027-12-31',
            'units' => 0,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('units', $validator->errors()->toArray());
    }

    public function test_store_creates_inventory_drug(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $request = StoreInventoryDrugRequest::create(
            '/admin/inventory-drugs',
            'POST',
            $this->validInventoryDrugData($clinicRoom, $drug)
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(InventoryDrugController::class)->store($request);

        $this->assertNotNull($response);

        $inventoryDrug = InventoryDrug::on('tenant')
            ->where('room_id', $clinicRoom->id)
            ->where('drug_id', $drug->id)
            ->first();

        $this->assertNotNull($inventoryDrug);
        $this->assertSame($clinicRoom->id, $inventoryDrug->room_id);
        $this->assertSame($drug->id, $inventoryDrug->drug_id);
        $this->assertSame('2027-12-31', (string) $inventoryDrug->expiry_date);
        $this->assertSame(10, (int) $inventoryDrug->units);
    }

    public function test_store_does_not_create_duplicate_drug(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $existing = $this->createInventoryDrug($clinicRoom, $drug, '2027-12-31', 10);

        $request = StoreInventoryDrugRequest::create(
            '/admin/inventory-drugs',
            'POST',
            $this->validInventoryDrugData($clinicRoom, $drug)
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(InventoryDrugController::class)->store($request);

        $this->assertNotNull($response);

        $this->assertSame(
            1,
            InventoryDrug::on('tenant')
                ->where('drug_id', $drug->id)
                ->count()
        );

        $existing->refresh();

        $this->assertSame(10, (int) $existing->units);
    }

    public function test_update_updates_inventory_drug(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();

        $inventoryDrug = $this->createInventoryDrug(
            $clinicRoom,
            $drug,
            '2027-12-31',
            10
        );

        $request = UpdateInventoryDrugRequest::create(
            '/admin/inventory-drugs/'.$inventoryDrug->id,
            'PUT',
            [
                'room_id' => $clinicRoom->id,
                'drug_id' => $drug->id,
                'expiry_date' => '2028-06-30',
                'units' => 25,
            ]
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(InventoryDrugController::class)->update($request, $inventoryDrug);

        $this->assertNotNull($response);

        $inventoryDrug->refresh();

        $this->assertSame('2028-06-30', (string) $inventoryDrug->expiry_date);
        $this->assertSame(25, (int) $inventoryDrug->units);
    }

    public function test_update_quantity_updates_units(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug, '2027-12-31', 10);

        $request = Request::create(
            '/admin/inventory-drugs/'.$inventoryDrug->id.'/quantity',
            'PATCH',
            [
                'quantity' => 25,
            ]
        );

        $response = app(InventoryDrugController::class)->updateQuantity($request, $inventoryDrug);

        $this->assertNotNull($response);

        $inventoryDrug->refresh();

        $this->assertSame(25, (int) $inventoryDrug->units);
    }

    public function test_update_quantity_requires_quantity(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $request = Request::create(
            '/admin/inventory-drugs/'.$inventoryDrug->id.'/quantity',
            'PATCH'
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(InventoryDrugController::class)->updateQuantity($request, $inventoryDrug);
    }

    public function test_update_expiry_date_updates_expiry_date(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug, '2027-12-31', 10);

        $request = Request::create(
            '/admin/inventory-drugs/'.$inventoryDrug->id.'/expiry-date',
            'PATCH',
            [
                'expirationDate' => '2028-06-30',
            ]
        );

        $response = app(InventoryDrugController::class)->updateExpiryDate($request, $inventoryDrug);

        $this->assertNotNull($response);

        $inventoryDrug->refresh();

        $this->assertSame('2028-06-30', (string) $inventoryDrug->expiry_date);
    }

    public function test_update_expiry_date_requires_expiration_date(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $request = Request::create(
            '/admin/inventory-drugs/'.$inventoryDrug->id.'/expiry-date',
            'PATCH'
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(InventoryDrugController::class)->updateExpiryDate($request, $inventoryDrug);
    }

    public function test_update_expiry_date_rejects_invalid_date(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $request = Request::create(
            '/admin/inventory-drugs/'.$inventoryDrug->id.'/expiry-date',
            'PATCH',
            [
                'expirationDate' => 'invalid-date',
            ]
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(InventoryDrugController::class)->updateExpiryDate($request, $inventoryDrug);
    }

    public function test_show_returns_null(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $response = app(InventoryDrugController::class)->show($inventoryDrug);

        $this->assertNull($response);
    }

    public function test_edit_returns_null(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $response = app(InventoryDrugController::class)->edit($inventoryDrug);

        $this->assertNull($response);
    }

    public function test_create_returns_null(): void
    {
        $this->createClinic();

        $response = app(InventoryDrugController::class)->create();

        $this->assertNull($response);
    }

    public function test_destroy_deletes_inventory_drug(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $drug = $this->createDrug();
        $inventoryDrug = $this->createInventoryDrug($clinicRoom, $drug);

        $inventoryDrugId = $inventoryDrug->id;

        $response = app(InventoryDrugController::class)->destroy($inventoryDrug);

        $this->assertNotNull($response);

        $this->assertDatabaseMissing('inventory_drugs', [
            'id' => $inventoryDrugId,
        ], 'tenant');
    }
}
