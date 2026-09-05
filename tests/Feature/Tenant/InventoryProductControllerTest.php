<?php

namespace Tests\Feature\Tenant;

use App\Http\Controllers\Admin\InventoryProductController;
use App\Http\Requests\StoreInventoryProductRequest;
use App\Http\Requests\UpdateInventoryProductRequest;
use App\Models\Clinic;
use App\Models\ClinicRoom;
use App\Models\InventoryProduct;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class InventoryProductControllerTest extends TestCase
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
            'inventory-product.view',
            'inventory-product.create',
            'inventory-product.update',
            'inventory-product.delete',
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

    private function createProduct(string $name = 'Prodotto 1'): Product
    {
        $product = new Product;
        $product->setConnection('tenant');
        $product->name = $name;
        $product->unit_price = '5.50';
        $product->save();

        return $product;
    }

    private function createInventoryProduct(ClinicRoom $clinicRoom, Product $product, string $expiryDate = '2027-12-31', int $units = 10): InventoryProduct
    {
        $inventoryProduct = new InventoryProduct;
        $inventoryProduct->setConnection('tenant');
        $inventoryProduct->room_id = $clinicRoom->id;
        $inventoryProduct->product_id = $product->id;
        $inventoryProduct->expiry_date = $expiryDate;
        $inventoryProduct->units = $units;
        $inventoryProduct->save();

        return $inventoryProduct;
    }

    private function validInventoryProductData(ClinicRoom $clinicRoom, Product $product): array
    {
        return [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
            'expiry_date' => '2027-12-31',
            'units' => 10,
        ];
    }

    public function test_policy_view_any_requires_permission(): void
    {
        $user = $this->createUser();

        $this->assertFalse(
            app(\App\Policies\InventoryProductPolicy::class)->viewAny($user)
        );

        $user->givePermissionTo('inventory-product.view');

        $this->assertTrue(
            app(\App\Policies\InventoryProductPolicy::class)->viewAny($user)
        );
    }

    public function test_policy_view_requires_permission(): void
    {
        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $policy = app(\App\Policies\InventoryProductPolicy::class);

        $this->assertFalse($policy->view($user, $inventoryProduct));

        $user->givePermissionTo('inventory-product.view');

        $this->assertTrue($policy->view($user, $inventoryProduct));
    }

    public function test_policy_create_requires_permission(): void
    {
        $user = $this->createUser();

        $policy = app(\App\Policies\InventoryProductPolicy::class);

        $this->assertFalse($policy->create($user));

        $user->givePermissionTo('inventory-product.create');

        $this->assertTrue($policy->create($user));
    }

    public function test_policy_update_requires_permission(): void
    {
        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $policy = app(\App\Policies\InventoryProductPolicy::class);

        $this->assertFalse($policy->update($user, $inventoryProduct));

        $user->givePermissionTo('inventory-product.update');

        $this->assertTrue($policy->update($user, $inventoryProduct));
    }

    public function test_policy_delete_requires_permission(): void
    {
        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $policy = app(\App\Policies\InventoryProductPolicy::class);

        $this->assertFalse($policy->delete($user, $inventoryProduct));

        $user->givePermissionTo('inventory-product.delete');

        $this->assertTrue($policy->delete($user, $inventoryProduct));
    }

    public function test_store_request_requires_expiry_date(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => 999999,
            'product_id' => $product->id,
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

    public function test_store_request_rejects_invalid_product_id(): void
    {
        $clinicRoom = $this->createClinicRoom();

        $request = StoreInventoryProductRequest::create('/admin/inventory-products', 'POST', [
            'room_id' => $clinicRoom->id,
            'product_id' => 999999,
            'expiry_date' => '2027-12-31',
            'units' => 10,
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('product_id', $validator->errors()->toArray());
    }

    public function test_update_request_requires_expiry_date(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();

        $request = UpdateInventoryProductRequest::create('/admin/inventory-products/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = UpdateInventoryProductRequest::create('/admin/inventory-products/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = UpdateInventoryProductRequest::create('/admin/inventory-products/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = UpdateInventoryProductRequest::create('/admin/inventory-products/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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
        $product = $this->createProduct();

        $request = UpdateInventoryProductRequest::create('/admin/inventory-products/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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

    public function test_update_request_rejects_negative_units(): void
    {
        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();

        $request = UpdateInventoryProductRequest::create('/admin/inventory-products/1', 'PUT', [
            'room_id' => $clinicRoom->id,
            'product_id' => $product->id,
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

    public function test_store_creates_inventory_product(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();

        $request = StoreInventoryProductRequest::create(
            '/admin/inventory-products',
            'POST',
            $this->validInventoryProductData($clinicRoom, $product)
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(InventoryProductController::class)->store($request);

        $this->assertNotNull($response);

        $inventoryProduct = InventoryProduct::on('tenant')
            ->where('room_id', $clinicRoom->id)
            ->where('product_id', $product->id)
            ->first();

        $this->assertNotNull($inventoryProduct);
        $this->assertSame($clinicRoom->id, $inventoryProduct->room_id);
        $this->assertSame($product->id, $inventoryProduct->product_id);
        $this->assertSame('2027-12-31', (string) $inventoryProduct->expiry_date);
        $this->assertSame(10, (int) $inventoryProduct->units);
    }

    public function test_store_does_not_create_duplicate_product(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();

        $existing = $this->createInventoryProduct($clinicRoom, $product, '2027-12-31', 10);

        $request = StoreInventoryProductRequest::create(
            '/admin/inventory-products',
            'POST',
            $this->validInventoryProductData($clinicRoom, $product)
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(InventoryProductController::class)->store($request);

        $this->assertNotNull($response);

        $this->assertSame(
            1,
            InventoryProduct::on('tenant')
                ->where('product_id', $product->id)
                ->count()
        );

        $existing->refresh();

        $this->assertSame(10, (int) $existing->units);
    }

    public function test_update_updates_inventory_product(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();

        $inventoryProduct = $this->createInventoryProduct(
            $clinicRoom,
            $product,
            '2027-12-31',
            10
        );

        $request = UpdateInventoryProductRequest::create(
            '/admin/inventory-products/'.$inventoryProduct->id,
            'PUT',
            [
                'room_id' => $clinicRoom->id,
                'product_id' => $product->id,
                'expiry_date' => '2028-06-30',
                'units' => 25,
            ]
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(InventoryProductController::class)->update($request, $inventoryProduct);

        $this->assertNotNull($response);

        $inventoryProduct->refresh();

        $this->assertSame('2028-06-30', (string) $inventoryProduct->expiry_date);
        $this->assertSame(25, (int) $inventoryProduct->units);
    }

    public function test_update_quantity_updates_units(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product, '2027-12-31', 10);

        $request = Request::create(
            '/admin/inventory-products/'.$inventoryProduct->id.'/quantity',
            'PATCH',
            [
                'quantity' => 25,
            ]
        );

        $response = app(InventoryProductController::class)->updateQuantity($request, $inventoryProduct);

        $this->assertNotNull($response);

        $inventoryProduct->refresh();

        $this->assertSame(25, (int) $inventoryProduct->units);
    }

    public function test_update_quantity_requires_quantity(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $request = Request::create(
            '/admin/inventory-products/'.$inventoryProduct->id.'/quantity',
            'PATCH'
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(InventoryProductController::class)->updateQuantity($request, $inventoryProduct);
    }

    public function test_update_expiry_date_updates_expiry_date(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product, '2027-12-31', 10);

        $request = Request::create(
            '/admin/inventory-products/'.$inventoryProduct->id.'/expiry-date',
            'PATCH',
            [
                'expirationDate' => '2028-06-30',
            ]
        );

        $response = app(InventoryProductController::class)->updateExpiryDate($request, $inventoryProduct);

        $this->assertNotNull($response);

        $inventoryProduct->refresh();

        $this->assertSame('2028-06-30', (string) $inventoryProduct->expiry_date);
    }

    public function test_update_expiry_date_requires_expiration_date(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $request = Request::create(
            '/admin/inventory-products/'.$inventoryProduct->id.'/expiry-date',
            'PATCH'
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(InventoryProductController::class)->updateExpiryDate($request, $inventoryProduct);
    }

    public function test_update_expiry_date_rejects_invalid_date(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $request = Request::create(
            '/admin/inventory-products/'.$inventoryProduct->id.'/expiry-date',
            'PATCH',
            [
                'expirationDate' => 'invalid-date',
            ]
        );

        $this->expectException(\Illuminate\Validation\ValidationException::class);

        app(InventoryProductController::class)->updateExpiryDate($request, $inventoryProduct);
    }

    public function test_show_returns_null(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $response = app(InventoryProductController::class)->show($inventoryProduct);

        $this->assertNull($response);
    }

    public function test_edit_returns_null(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $response = app(InventoryProductController::class)->edit($inventoryProduct);

        $this->assertNull($response);
    }

    public function test_create_returns_null(): void
    {
        $this->createClinic();

        $response = app(InventoryProductController::class)->create();

        $this->assertNull($response);
    }

    public function test_destroy_deletes_inventory_product(): void
    {
        $this->createClinic();

        $clinicRoom = $this->createClinicRoom();
        $product = $this->createProduct();
        $inventoryProduct = $this->createInventoryProduct($clinicRoom, $product);

        $inventoryProductId = $inventoryProduct->id;

        $response = app(InventoryProductController::class)->destroy($inventoryProduct);

        $this->assertNotNull($response);

        $this->assertDatabaseMissing('inventory_products', [
            'id' => $inventoryProductId,
        ], 'tenant');
    }
}
