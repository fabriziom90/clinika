<?php

namespace Tests\Feature\Superadmin;

use App\Mail\PersonSetPasswordMail;
use App\Models\CentralUser;
use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withServerVariables([
            'HTTP_HOST' => 'localhost',
        ]);

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

        Role::on('tenant')->firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);

    }

    private function createSuperadmin(): CentralUser
    {
        $user = new CentralUser([
            'name' => 'Test Superadmin',
            'email' => 'superadmin-'.Str::lower(Str::random(8)).'@test.it',
            'password' => bcrypt('password'),
            'is_superadmin' => true,
        ]);

        $user->save();

        return $user;
    }

    private function authenticateSuperadmin(): CentralUser
    {
        $user = $this->createSuperadmin();

        Auth::guard('superadmin')->setUser($user);

        return $user;
    }

    private function createClinic(?string $name = null, bool $active = true): Clinic
    {
        return Clinic::on('central')->create([
            'uuid' => Str::uuid(),
            'name' => $name ?? 'Test Clinic '.Str::random(8),
            'slug' => 'test-'.Str::lower(Str::random(10)),
            'email' => 'clinic-'.Str::lower(Str::random(8)).'@test.it',
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
            'active' => $active,
        ]);
    }

    private function createAdmin(?string $email = null): User
    {
        $user = User::factory()->make([
            'email' => $email ?? 'admin-'.Str::lower(Str::random(8)).'@test.it',
        ]);

        $user->setConnection('tenant');
        $user->save();
        $user->assignRole('admin');

        return $user;
    }

    public function test_unauthenticated_user_cannot_access_admins(): void
    {
        $this->get(route('superadmin.admins.index'))
            ->assertRedirect();
    }

    public function test_index_returns_active_clinic_admins(): void
    {
        $this->authenticateSuperadmin();

        Clinic::on('central')->update(['active' => false]);

        $activeClinic = $this->createClinic('Active Clinic');
        $inactiveClinic = $this->createClinic('Inactive Clinic', false);

        $activeAdmin = $this->createAdmin('active-admin@test.it');
        $inactiveAdmin = $this->createAdmin('inactive-admin@test.it');

        $response = $this->get(route('superadmin.admins.index'));

        $response->assertSuccessful();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Superadmin/Admins/IndexAdmins')
            ->where('admins', function ($admins) use ($activeAdmin, $activeClinic, $inactiveAdmin, $inactiveClinic) {
                $admins = collect($admins);

                return $admins->contains(fn ($item) => $item['id'] === $activeAdmin->id
                    && $item['clinic_id'] === $activeClinic->id
                    && $item['clinic_name'] === $activeClinic->name
                )
                && ! $admins->contains(fn ($item) => $item['id'] === $inactiveAdmin->id
                    && $item['clinic_id'] === $inactiveClinic->id
                );
            })
            ->has('columns')
        );
    }

    public function test_create_returns_active_clinics(): void
    {
        $this->authenticateSuperadmin();

        $activeClinic = $this->createClinic('Active Create Clinic');
        $this->createClinic('Inactive Create Clinic', false);

        $response = $this->get(route('superadmin.admins.create'));

        $response->assertSuccessful();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Superadmin/Admins/CreateAdmin')
            ->where('clinics', function ($clinics) use ($activeClinic) {
                $clinics = collect($clinics);

                return $clinics->contains(fn ($clinic) => $clinic['id'] === $activeClinic->id
                    && $clinic['name'] === $activeClinic->name
                )
                && ! $clinics->contains(fn ($clinic) => $clinic['name'] === 'Inactive Create Clinic'
                );
            })
        );
    }

    public function test_store_creates_admin_in_selected_tenant(): void
    {
        Mail::fake();

        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $email = 'new-admin-'.Str::lower(Str::random(8)).'@test.it';

        $response = $this->post(route('superadmin.admins.store'), [
            'clinic_id' => $clinic->id,
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => $email,
        ]);

        $response->assertRedirect(route('superadmin.admins.index'));

        $user = User::on('tenant')
            ->where('email_hash', hash('sha256', mb_strtolower(trim($email))))
            ->first();

        $this->assertNotNull($user);
        $this->assertSame('Mario', $user->name);
        $this->assertSame('Rossi', $user->surname);
        $this->assertTrue($user->hasRole('admin'));

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $user->id,
        ], 'tenant');

        Mail::assertSent(PersonSetPasswordMail::class, fn ($mail) => $mail->hasTo($email));
    }

    public function test_store_requires_valid_data(): void
    {
        $this->authenticateSuperadmin();

        $response = $this->post(route('superadmin.admins.store'), [
            'clinic_id' => 999999999,
            'name' => '',
            'surname' => '',
            'email' => 'invalid-email',
        ]);

        $response->assertSessionHasErrors([
            'clinic_id',
            'name',
            'surname',
            'email',
        ]);
    }

    public function test_store_rejects_duplicate_email_in_selected_clinic(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $email = 'duplicate-admin-'.Str::lower(Str::random(8)).'@test.it';

        $this->createAdmin($email);

        $response = $this->post(route('superadmin.admins.store'), [
            'clinic_id' => $clinic->id,
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'email' => $email,
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertSame(
            1,
            User::on('tenant')
                ->where('email_hash', hash('sha256', mb_strtolower(trim($email))))
                ->count()
        );
    }

    public function test_show_returns_admin(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic('Show Clinic');
        $email = 'show-admin-'.Str::lower(Str::random(8)).'@test.it';
        $admin = $this->createAdmin($email);

        $admin->update([
            'name' => 'Mario',
            'surname' => 'Rossi',
        ]);

        $response = $this->get(route('superadmin.admins.show', [
            'clinic' => $clinic->id,
            'admin' => $admin->id,
        ]));

        $response->assertSuccessful();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Superadmin/Admins/ShowAdmin')
            ->where('admin.id', $admin->id)
            ->where('admin.clinic_id', $clinic->id)
            ->where('admin.clinic_name', $clinic->name)
            ->where('admin.name', 'Mario')
            ->where('admin.surname', 'Rossi')
            ->where('admin.email', $email)
        );
    }

    public function test_show_cannot_access_admin_from_another_role(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $user = User::factory()->make([
            'email' => 'user-'.Str::lower(Str::random(8)).'@test.it',
        ]);

        $user->setConnection('tenant');
        $user->save();

        $response = $this->get(route('superadmin.admins.show', [
            'clinic' => $clinic->id,
            'admin' => $user->id,
        ]));

        $response->assertNotFound();
    }

    public function test_edit_returns_admin_and_clinics(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic('Edit Clinic');
        $otherClinic = $this->createClinic('Other Clinic');

        $admin = $this->createAdmin(
            'edit-admin-'.Str::lower(Str::random(8)).'@test.it'
        );

        $response = $this->get(route('superadmin.admins.edit', [
            'clinic' => $clinic->id,
            'admin' => $admin->id,
        ]));

        $response->assertSuccessful();

        $response->assertInertia(fn (Assert $page) => $page
            ->component('Superadmin/Admins/EditAdmin')
            ->where('admin.id', $admin->id)
            ->where('admin.clinic_id', $clinic->id)
            ->where('admin.clinic_name', $clinic->name)
            ->where('clinics', function ($clinics) use ($clinic, $otherClinic) {
                $clinics = collect($clinics);

                return $clinics->contains(fn ($item) => $item['id'] === $clinic->id)
                    && $clinics->contains(fn ($item) => $item['id'] === $otherClinic->id);
            })
        );
    }

    public function test_update_updates_admin(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $oldEmail = 'old-admin-'.Str::lower(Str::random(8)).'@test.it';
        $newEmail = 'updated-admin-'.Str::lower(Str::random(8)).'@test.it';

        $admin = $this->createAdmin($oldEmail);

        $response = $this->put(route('superadmin.admins.update', [
            'clinic' => $clinic->id,
            'admin' => $admin->id,
        ]), [
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'email' => $newEmail,
        ]);

        $response->assertRedirect(route('superadmin.admins.index'));

        $admin = User::on('tenant')->findOrFail($admin->id);

        $this->assertSame('Luigi', $admin->name);
        $this->assertSame('Bianchi', $admin->surname);
        $this->assertSame($newEmail, $admin->email);
        $this->assertSame(
            hash('sha256', mb_strtolower(trim($newEmail))),
            $admin->email_hash
        );
    }

    public function test_update_rejects_duplicate_email(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $admin = $this->createAdmin(
            'first-admin-'.Str::lower(Str::random(8)).'@test.it'
        );

        $otherAdmin = $this->createAdmin(
            'second-admin-'.Str::lower(Str::random(8)).'@test.it'
        );

        $response = $this->put(route('superadmin.admins.update', [
            'clinic' => $clinic->id,
            'admin' => $admin->id,
        ]), [
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => $otherAdmin->email,
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_destroy_deletes_admin(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();
        $admin = $this->createAdmin();

        $adminId = $admin->id;

        $response = $this->delete(route('superadmin.admins.destroy', [
            'clinic' => $clinic->id,
            'admin' => $adminId,
        ]));

        $response->assertRedirect(route('superadmin.admins.index'));

        $this->assertDatabaseMissing('users', [
            'id' => $adminId,
        ], 'tenant');
    }

    public function test_send_reset_email_creates_new_token_and_sends_mail(): void
    {
        Mail::fake();

        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $email = 'reset-admin-'.Str::lower(Str::random(8)).'@test.it';
        $admin = $this->createAdmin($email);

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->where('user_id', $admin->id)
            ->delete();

        $response = $this->post(route('superadmin.admins.send-reset-email', [
            'clinic' => $clinic->id,
            'admin' => $admin->id,
        ]));

        $response->assertSessionHas('toast');

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $admin->id,
        ], 'tenant');

        Mail::assertSent(PersonSetPasswordMail::class, fn ($mail) => $mail->hasTo($email));
    }

    public function test_show_returns_not_found_for_non_existing_clinic(): void
    {
        $this->authenticateSuperadmin();

        $response = $this->get(route('superadmin.admins.show', [
            'clinic' => 999999999,
            'admin' => 999999999,
        ]));

        $response->assertNotFound();
    }

    public function test_update_returns_not_found_for_non_admin_user(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $user = User::factory()->make([
            'email' => 'ordinary-user-'.Str::lower(Str::random(8)).'@test.it',
        ]);

        $user->setConnection('tenant');
        $user->save();

        $response = $this->put(route('superadmin.admins.update', [
            'clinic' => $clinic->id,
            'admin' => $user->id,
        ]), [
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'changed-'.Str::lower(Str::random(8)).'@test.it',
        ]);

        $response->assertNotFound();
    }

    public function test_destroy_returns_not_found_for_non_admin_user(): void
    {
        $this->authenticateSuperadmin();

        $clinic = $this->createClinic();

        $user = User::factory()->make([
            'email' => 'ordinary-delete-'.Str::lower(Str::random(8)).'@test.it',
        ]);

        $user->setConnection('tenant');
        $user->save();

        $response = $this->delete(route('superadmin.admins.destroy', [
            'clinic' => $clinic->id,
            'admin' => $user->id,
        ]));

        $response->assertNotFound();
    }
}
