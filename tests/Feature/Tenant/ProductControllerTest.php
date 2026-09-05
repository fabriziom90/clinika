<?php

namespace Tests\Feature\Tenant;

use App\Http\Controllers\Admin\ProductController;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Clinic;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ProductControllerTest extends TestCase
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
            'product.view',
            'product.create',
            'product.update',
            'product.delete',
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

    private function createProduct(string $name = 'Prodotto medico'): Product
    {
        $product = new Product;
        $product->setConnection('tenant');
        $product->name = $name;
        $product->unit_price = '5.50';
        $product->save();

        return $product;
    }

    private function validProductData(): array
    {
        return [
            'name' => 'Nuovo prodotto medico',
            'unit_price' => '8.50',
        ];
    }

    public function test_policy_view_any_requires_permission(): void
    {
        $user = $this->createUser();

        $this->assertFalse(
            app(\App\Policies\ProductPolicy::class)->viewAny($user)
        );

        $user->givePermissionTo('product.view');

        $this->assertTrue(
            app(\App\Policies\ProductPolicy::class)->viewAny($user)
        );
    }

    public function test_policy_view_requires_permission(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        $policy = app(\App\Policies\ProductPolicy::class);

        $this->assertFalse($policy->view($user, $product));

        $user->givePermissionTo('product.view');

        $this->assertTrue($policy->view($user, $product));
    }

    public function test_policy_create_requires_permission(): void
    {
        $user = $this->createUser();

        $policy = app(\App\Policies\ProductPolicy::class);

        $this->assertFalse($policy->create($user));

        $user->givePermissionTo('product.create');

        $this->assertTrue($policy->create($user));
    }

    public function test_policy_update_requires_permission(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        $policy = app(\App\Policies\ProductPolicy::class);

        $this->assertFalse($policy->update($user, $product));

        $user->givePermissionTo('product.update');

        $this->assertTrue($policy->update($user, $product));
    }

    public function test_policy_delete_requires_permission(): void
    {
        $user = $this->createUser();
        $product = $this->createProduct();

        $policy = app(\App\Policies\ProductPolicy::class);

        $this->assertFalse($policy->delete($user, $product));

        $user->givePermissionTo('product.delete');

        $this->assertTrue($policy->delete($user, $product));
    }

    public function test_store_request_requires_name(): void
    {
        $request = StoreProductRequest::create('/admin/products', 'POST', [
            'unit_price' => '8.50',
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
        $request = StoreProductRequest::create('/admin/products', 'POST', [
            'name' => str_repeat('a', 121),
            'unit_price' => '8.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_non_numeric_unit_price(): void
    {
        $request = StoreProductRequest::create('/admin/products', 'POST', [
            'name' => 'Prodotto medico',
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
        $request = StoreProductRequest::create('/admin/products', 'POST', [
            'name' => 'Prodotto medico',
            'unit_price' => '-1.00',
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
        $request = StoreProductRequest::create('/admin/products', 'POST', [
            'name' => 'Prodotto medico',
            'unit_price' => '8.555',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    public function test_update_request_requires_name(): void
    {
        $request = UpdateProductRequest::create('/admin/products/1', 'PUT', [
            'unit_price' => '8.50',
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
        $request = UpdateProductRequest::create('/admin/products/1', 'PUT', [
            'name' => str_repeat('a', 121),
            'unit_price' => '8.50',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_non_numeric_unit_price(): void
    {
        $request = UpdateProductRequest::create('/admin/products/1', 'PUT', [
            'name' => 'Prodotto medico',
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

    public function test_update_request_rejects_negative_unit_price(): void
    {
        $request = UpdateProductRequest::create('/admin/products/1', 'PUT', [
            'name' => 'Prodotto medico',
            'unit_price' => '-1.00',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_more_than_two_decimal_places(): void
    {
        $request = UpdateProductRequest::create('/admin/products/1', 'PUT', [
            'name' => 'Prodotto medico',
            'unit_price' => '8.555',
        ]);

        $validator = Validator::make(
            $request->all(),
            $request->rules(),
            $request->messages()
        );

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('unit_price', $validator->errors()->toArray());
    }

    public function test_index_returns_inertia_response(): void
    {
        $clinic = $this->createClinic();

        $product = $this->createProduct();

        $user = $this->createUser();
        $user->givePermissionTo('product.view');

        $response = $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/products");

        $response->assertSuccessful();

        $response->assertInertia(fn ($page) => $page
            ->component('Products/IndexProducts')
            ->has('products')
            ->where('columns', [
                'id' => 'ID',
                'name' => 'Nome',
                'unit_price' => 'Prezzo unitario',
            ])
            ->where('products', fn ($products) => collect($products)->contains(
                fn ($item) => $item['id'] === $product->id
                    && $item['name'] === 'Prodotto medico'
            ))
        );
    }

    public function test_store_creates_product(): void
    {
        $this->createClinic();

        $request = StoreProductRequest::create(
            '/admin/products',
            'POST',
            $this->validProductData()
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(ProductController::class)->store($request);

        $this->assertNotNull($response);

        $product = Product::on('tenant')
            ->where('name', 'Nuovo prodotto medico')
            ->latest('id')
            ->first();

        $this->assertNotNull($product);
        $this->assertSame('Nuovo prodotto medico', $product->name);
        $this->assertEquals(8.50, $product->unit_price);
    }

    public function test_update_updates_product(): void
    {
        $this->createClinic();

        $product = $this->createProduct();

        $request = UpdateProductRequest::create(
            '/admin/products/'.$product->id,
            'PUT',
            [
                'name' => 'Prodotto medico modificato',
                'unit_price' => '12.75',
            ]
        );

        $request->setUserResolver(fn () => $this->createUser());
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        $response = app(ProductController::class)->update($request, $product);

        $this->assertNotNull($response);

        $product->refresh();

        $this->assertSame('Prodotto medico modificato', $product->name);
        $this->assertEquals(12.75, $product->unit_price);
    }

    public function test_destroy_deletes_product(): void
    {
        $this->createClinic();

        $product = $this->createProduct();
        $productId = $product->id;

        $response = app(ProductController::class)->destroy($product);

        $this->assertNotNull($response);

        $this->assertDatabaseMissing('products', [
            'id' => $productId,
        ], 'tenant');
    }

    public function test_create_returns_null(): void
    {
        $this->createClinic();

        $response = app(ProductController::class)->create();

        $this->assertNull($response);
    }

    public function test_show_returns_null(): void
    {
        $this->createClinic();

        $product = $this->createProduct();

        $response = app(ProductController::class)->show($product);

        $this->assertNull($response);
    }

    public function test_edit_returns_null(): void
    {
        $this->createClinic();

        $product = $this->createProduct();

        $response = app(ProductController::class)->edit($product);

        $this->assertNull($response);
    }
}
