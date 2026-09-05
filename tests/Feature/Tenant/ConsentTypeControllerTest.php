<?php

namespace Tests\Feature\Tenant;

use App\Http\Controllers\Admin\ConsentTypeController;
use App\Http\Requests\StoreConsentTypeRequest;
use App\Http\Requests\UpdateConsentTypeRequest;
use App\Models\Clinic;
use App\Models\ConsentType;
use App\Models\User;
use App\Policies\ConsentTypePolicy;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ConsentTypeControllerTest extends TestCase
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
            'consent-type.view',
            'consent-type.create',
            'consent-type.update',
            'consent-type.delete',
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
            'name' => 'Test Clinic '.Str::random(8),
            'slug' => 'test-'.Str::lower(Str::random(8)),
            'email' => Str::lower(Str::random(8)).'@example.com',
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

    private function givePermission(User $user, string $permission): void
    {
        $permission = Permission::on('tenant')
            ->where('name', $permission)
            ->where('guard_name', 'web')
            ->firstOrFail();

        $user->givePermissionTo($permission);
    }

    private function createConsentType(): ConsentType
    {
        $consentType = new ConsentType;
        $consentType->setConnection('tenant');
        $consentType->name = 'Consenso privacy '.Str::random(12);
        $consentType->code = Str::slug($consentType->name);
        $consentType->description = 'Descrizione del consenso';
        $consentType->acquisition_method = 'paper';
        $consentType->is_required = true;
        $consentType->is_active = true;
        $consentType->save();

        return $consentType;
    }

    /*
    |--------------------------------------------------------------------------
    | Policy
    |--------------------------------------------------------------------------
    */

    public function test_user_without_view_permission_cannot_access_consent_types(): void
    {
        $user = $this->createUser();

        $policy = new ConsentTypePolicy;

        $this->assertFalse($policy->viewAny($user));
    }

    public function test_user_with_view_permission_can_access_consent_types(): void
    {
        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.view');

        $policy = new ConsentTypePolicy;

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_user_without_create_permission_cannot_create_consent_type(): void
    {
        $user = $this->createUser();

        $policy = new ConsentTypePolicy;

        $this->assertFalse($policy->create($user));
    }

    public function test_user_with_create_permission_can_create_consent_type(): void
    {
        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.create');

        $policy = new ConsentTypePolicy;

        $this->assertTrue($policy->create($user));
    }

    public function test_user_without_view_permission_cannot_view_consent_type(): void
    {
        $user = $this->createUser();
        $consentType = $this->createConsentType();

        $policy = new ConsentTypePolicy;

        $this->assertFalse($policy->view($user, $consentType));
    }

    public function test_user_with_view_permission_can_view_consent_type(): void
    {
        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.view');

        $consentType = $this->createConsentType();

        $policy = new ConsentTypePolicy;

        $this->assertTrue($policy->view($user, $consentType));
    }

    public function test_user_without_update_permission_cannot_update_consent_type(): void
    {
        $user = $this->createUser();
        $consentType = $this->createConsentType();

        $policy = new ConsentTypePolicy;

        $this->assertFalse($policy->update($user, $consentType));
    }

    public function test_user_with_update_permission_can_update_consent_type(): void
    {
        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.update');

        $consentType = $this->createConsentType();

        $policy = new ConsentTypePolicy;

        $this->assertTrue($policy->update($user, $consentType));
    }

    public function test_user_without_delete_permission_cannot_delete_consent_type(): void
    {
        $user = $this->createUser();
        $consentType = $this->createConsentType();

        $policy = new ConsentTypePolicy;

        $this->assertFalse($policy->delete($user, $consentType));
    }

    public function test_user_with_delete_permission_can_delete_consent_type(): void
    {
        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.delete');

        $consentType = $this->createConsentType();

        $policy = new ConsentTypePolicy;

        $this->assertTrue($policy->delete($user, $consentType));
    }

    /*
    |--------------------------------------------------------------------------
    | Controller
    |--------------------------------------------------------------------------
    */

    public function test_index_returns_consent_types_page(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.view');

        $this->actingAs($user, 'web');

        $controller = app(ConsentTypeController::class);

        $response = $controller->index();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_create_returns_create_page(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.create');

        $this->actingAs($user, 'web');

        $controller = app(ConsentTypeController::class);

        $response = $controller->create();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_store_creates_consent_type(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->givePermission($user, 'consent-type.create');

        $this->actingAs($user, 'web');

        $name = 'Nuovo consenso '.Str::random(12);

        $request = StoreConsentTypeRequest::create(
            route('admin.consent-types.store'),
            'POST',
            [
                'name' => $name,
                'description' => 'Descrizione',
                'acquisition_method' => 'paper',
                'is_required' => true,
                'is_active' => true,
            ]
        );

        $request->setUserResolver(fn () => $user);
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));

        $request->validateResolved();

        $controller = app(ConsentTypeController::class);

        $response = $controller->store($request);

        $this->assertNotNull($response);

        $this->assertDatabaseHas('consent_types', [
            'name' => $name,
            'code' => Str::slug($name),
            'description' => 'Descrizione',
            'acquisition_method' => 'paper',
            'is_required' => 1,
            'is_active' => 1,
        ], 'tenant');
    }

    public function test_show_returns_consent_type_page(): void
    {
        $this->createClinic();

        $user = $this->createUser();

        $consentType = $this->createConsentType();

        $this->actingAs($user, 'web');

        $controller = app(ConsentTypeController::class);

        $response = $controller->show($consentType);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_edit_returns_consent_type_page(): void
    {
        $this->createClinic();

        $user = $this->createUser();

        $consentType = $this->createConsentType();

        $this->actingAs($user, 'web');

        $controller = app(ConsentTypeController::class);

        $response = $controller->edit($consentType);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_update_updates_consent_type(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $consentType = $this->createConsentType();

        $newName = 'Consenso modificato '.Str::random(12);

        $request = UpdateConsentTypeRequest::create(
            route('admin.consent-types.update', [
                'consent_type' => $consentType->id,
            ]),
            'PUT',
            [
                'name' => $newName,
                'description' => 'Descrizione modificata',
                'acquisition_method' => 'upload',
                'is_required' => false,
                'is_active' => true,
            ]
        );

        $request->setUserResolver(fn () => $user);
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));

        $request->validateResolved();

        $controller = app(ConsentTypeController::class);

        $response = $controller->update($request, $consentType);

        $this->assertNotNull($response);

        $this->assertDatabaseHas('consent_types', [
            'id' => $consentType->id,
            'name' => $newName,
            'code' => Str::slug($newName),
            'description' => 'Descrizione modificata',
            'acquisition_method' => 'upload',
            'is_required' => 0,
            'is_active' => 1,
        ], 'tenant');
    }

    public function test_destroy_deletes_consent_type(): void
    {
        $this->createClinic();

        $user = $this->createUser();

        $consentType = $this->createConsentType();

        $controller = app(ConsentTypeController::class);

        $response = $controller->destroy($consentType);

        $this->assertNotNull($response);

        $this->assertSoftDeleted('consent_types', [
            'id' => $consentType->id,
        ], 'tenant');
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function test_store_requires_name(): void
    {
        $request = new StoreConsentTypeRequest;

        $validator = validator([
            'description' => 'Descrizione',
            'acquisition_method' => 'paper',
            'is_required' => true,
            'is_active' => true,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_store_rejects_invalid_acquisition_method(): void
    {
        $request = new StoreConsentTypeRequest;

        $validator = validator([
            'name' => 'Consenso '.Str::random(8),
            'description' => 'Descrizione',
            'acquisition_method' => 'invalid',
            'is_required' => true,
            'is_active' => true,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('acquisition_method'));
    }

    public function test_store_requires_boolean_fields(): void
    {
        $request = new StoreConsentTypeRequest;

        $validator = validator([
            'name' => 'Consenso '.Str::random(8),
            'description' => 'Descrizione',
            'acquisition_method' => 'paper',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('is_required'));
        $this->assertTrue($validator->errors()->has('is_active'));
    }

    public function test_update_requires_name(): void
    {
        $request = new UpdateConsentTypeRequest;

        $validator = validator([
            'description' => 'Descrizione',
            'acquisition_method' => 'paper',
            'is_required' => true,
            'is_active' => true,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('name'));
    }

    public function test_update_rejects_invalid_acquisition_method(): void
    {
        $request = new UpdateConsentTypeRequest;

        $validator = validator([
            'name' => 'Consenso modificato '.Str::random(8),
            'description' => 'Descrizione',
            'acquisition_method' => 'invalid',
            'is_required' => true,
            'is_active' => true,
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('acquisition_method'));
    }

    public function test_update_requires_boolean_fields(): void
    {
        $request = new UpdateConsentTypeRequest;

        $validator = validator([
            'name' => 'Consenso modificato '.Str::random(8),
            'description' => 'Descrizione',
            'acquisition_method' => 'paper',
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('is_required'));
        $this->assertTrue($validator->errors()->has('is_active'));
    }
}
