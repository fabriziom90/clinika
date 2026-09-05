<?php

namespace Tests\Feature\Tenant;

use App\Http\Controllers\Admin\ClinicRoomController;
use App\Http\Requests\StoreClinicRoomRequest;
use App\Http\Requests\UpdateClinicRoomRequest;
use App\Models\Clinic;
use App\Models\ClinicRoom;
use App\Models\User;
use App\Policies\ClinicRoomPolicy;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class ClinicRoomControllerTest extends TestCase
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
            'clinic-room.view',
            'clinic-room.create',
            'clinic-room.update',
            'clinic-room.delete',
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
            'uuid' => (string) Str::uuid(),
            'name' => 'Clinic '.Str::random(10),
            'slug' => Str::slug('clinic-'.Str::random(10)),
            'email' => Str::random(10).'@example.com',
            'database' => 'clinika_test_tenant',
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', 3306),
            'db_username' => env('DB_USERNAME', 'root'),
            'db_password' => env('DB_PASSWORD', ''),
        ]);

        config(['database.connections.tenant.database' => 'clinika_test_tenant']);

        $this->app['db']->purge('tenant');
        $this->app['db']->reconnect('tenant');

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
        $permissionModel = Permission::where('name', $permission)
            ->where('guard_name', 'web')
            ->firstOrFail();

        $user->givePermissionTo($permissionModel);
    }

    private function createPermissions(): void
    {
        foreach ([
            'clinic-room.view',
            'clinic-room.create',
            'clinic-room.update',
            'clinic-room.delete',
        ] as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }
    }

    private function createClinicRoom(): ClinicRoom
    {
        $clinicRoom = new ClinicRoom;
        $clinicRoom->setConnection('tenant');
        $clinicRoom->name = 'Stanza '.Str::random(10);
        $clinicRoom->save();

        return $clinicRoom;
    }

    /*
    |--------------------------------------------------------------------------
    | Policy
    |--------------------------------------------------------------------------
    */

    public function test_policy_view_any_requires_permission(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $policy = new ClinicRoomPolicy;

        $this->assertFalse($policy->viewAny($user));

        $this->givePermission($user, 'clinic-room.view');

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_policy_view_requires_permission(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $policy = new ClinicRoomPolicy;

        $this->assertFalse($policy->view($user, $clinicRoom));

        $this->givePermission($user, 'clinic-room.view');

        $this->assertTrue($policy->view($user, $clinicRoom));
    }

    public function test_policy_create_requires_permission(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $policy = new ClinicRoomPolicy;

        $this->assertFalse($policy->create($user));

        $this->givePermission($user, 'clinic-room.create');

        $this->assertTrue($policy->create($user));
    }

    public function test_policy_update_requires_permission(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $policy = new ClinicRoomPolicy;

        $this->assertFalse($policy->update($user, $clinicRoom));

        $this->givePermission($user, 'clinic-room.update');

        $this->assertTrue($policy->update($user, $clinicRoom));
    }

    public function test_policy_delete_requires_permission(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $clinicRoom = $this->createClinicRoom();
        $policy = new ClinicRoomPolicy;

        $this->assertFalse($policy->delete($user, $clinicRoom));

        $this->givePermission($user, 'clinic-room.delete');

        $this->assertTrue($policy->delete($user, $clinicRoom));
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function test_store_request_requires_name(): void
    {
        $request = new StoreClinicRoomRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_store_request_rejects_name_longer_than_255_characters(): void
    {
        $request = new StoreClinicRoomRequest;

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_update_request_requires_name(): void
    {
        $request = new UpdateClinicRoomRequest;

        $validator = Validator::make([], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    public function test_update_request_rejects_name_longer_than_255_characters(): void
    {
        $request = new UpdateClinicRoomRequest;

        $validator = Validator::make([
            'name' => str_repeat('a', 256),
        ], $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('name', $validator->errors()->toArray());
    }

    /*
    |--------------------------------------------------------------------------
    | Controller
    |--------------------------------------------------------------------------
    */

    public function test_index_returns_inertia_response(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->actingAs($user, 'web');

        $controller = app(ClinicRoomController::class);

        $response = $controller->index();

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_create_returns_null(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->actingAs($user, 'web');

        $controller = app(ClinicRoomController::class);

        $this->assertNull($controller->create());
    }

    public function test_store_creates_clinic_room(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $this->givePermission($user, 'clinic-room.create');

        $this->actingAs($user, 'web');

        $name = 'Nuova stanza '.Str::random(12);

        $request = StoreClinicRoomRequest::create(
            route('admin.clinic-rooms.store'),
            'POST',
            [
                'name' => $name,
            ]
        );

        $request->setUserResolver(fn () => $user);
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));

        $request->validateResolved();

        $controller = app(ClinicRoomController::class);

        $response = $controller->store($request);

        $this->assertNotNull($response);

        $this->assertDatabaseHas('clinic_rooms', [
            'name' => $name,
        ], 'tenant');
    }

    public function test_show_returns_inertia_response(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->actingAs($user, 'web');

        $clinicRoom = $this->createClinicRoom();

        $controller = app(ClinicRoomController::class);

        $response = $controller->show($clinicRoom);

        $this->assertInstanceOf(Response::class, $response);
    }

    public function test_edit_returns_null(): void
    {
        $this->createClinic();

        $user = $this->createUser();
        $this->actingAs($user, 'web');

        $clinicRoom = $this->createClinicRoom();

        $controller = app(ClinicRoomController::class);

        $this->assertNull($controller->edit($clinicRoom));
    }

    public function test_update_updates_clinic_room(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $this->givePermission($user, 'clinic-room.update');

        $this->actingAs($user, 'web');

        $clinicRoom = $this->createClinicRoom();

        $newName = 'Stanza modificata '.Str::random(12);

        $request = UpdateClinicRoomRequest::create(
            route('admin.clinic-rooms.update', [
                'clinic_room' => $clinicRoom->id,
            ]),
            'PUT',
            [
                'name' => $newName,
            ]
        );

        $request->setUserResolver(fn () => $user);
        $request->setContainer($this->app);
        $request->setRedirector(app('redirect'));

        $request->validateResolved();

        $controller = app(ClinicRoomController::class);

        $response = $controller->update($request, $clinicRoom);

        $this->assertNotNull($response);

        $this->assertDatabaseHas('clinic_rooms', [
            'id' => $clinicRoom->id,
            'name' => $newName,
        ], 'tenant');
    }

    public function test_destroy_deletes_clinic_room(): void
    {
        $this->createClinic();
        $this->createPermissions();

        $user = $this->createUser();
        $this->givePermission($user, 'clinic-room.delete');

        $this->actingAs($user, 'web');

        $clinicRoom = $this->createClinicRoom();

        $controller = app(ClinicRoomController::class);

        $response = $controller->destroy($clinicRoom);

        $this->assertNotNull($response);

        $this->assertNull($clinicRoom->fresh());
    }
}
