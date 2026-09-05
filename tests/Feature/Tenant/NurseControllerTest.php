<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\Nationality;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NurseControllerTest extends TestCase
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

        Role::on('tenant')->firstOrCreate([
            'name' => 'nurse',
            'guard_name' => 'web',
        ]);

        foreach ([
            'nurse.view',
            'nurse.create',
            'nurse.update',
            'nurse.delete',
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

    private function createNationality(): Nationality
    {
        $nationality = new Nationality;
        $nationality->setConnection('tenant');
        $nationality->name = 'Italiana';
        $nationality->state = 'Italia';
        $nationality->save();

        return $nationality;
    }

    private function createNurse(): Nurse
    {
        $user = $this->createUser();

        $user->update([
            'name' => 'Mario',
            'surname' => 'Rossi',
        ]);

        $nationality = $this->createNationality();

        return Nurse::on('tenant')->create([
            'user_id' => $user->id,
            'personal_code' => 'RSSMRA80A01H501Z',
            'vat' => '12345678901',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'genre' => 'M',
            'pec' => 'mario.rossi@pec.example.com',
            'cap' => '00100',
            'nationality_id' => $nationality->id,
        ]);
    }

    private function validNurseData(?int $nationalityId = null): array
    {
        $nationalityId ??= $this->createNationality()->id;

        return [
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'personal_code' => 'BNCLGU80A01H501Z',
            'vat' => '98765432109',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Milano 10',
            'phone' => '3339876543',
            'email' => 'luigi.'.Str::lower(Str::random(8)).'@example.com',
            'genre' => 'M',
            'pec' => 'luigi.bianchi@pec.example.com',
            'nationality_id' => $nationalityId,
            'zip_code' => '00100',
        ];
    }

    public function test_user_without_view_permission_cannot_access_nurses(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_nurses(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.view');

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_access_nurse_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses/create")
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_access_nurse_create_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.create');

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses/create")
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $data = $this->validNurseData();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/nurses", $data)
            ->assertForbidden();

        $this->assertDatabaseMissing('nurses', [
            'personal_code' => 'BNCLGU80A01H501Z',
        ], 'tenant');
    }

    public function test_user_with_create_permission_can_create_nurse(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.create');

        $nationality = $this->createNationality();

        $data = $this->validNurseData($nationality->id);

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/nurses", $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $createdUser = User::on('tenant')
            ->where('email_hash', hash('sha256', mb_strtolower(trim($data['email']))))
            ->first();

        $this->assertNotNull($createdUser);

        $nurse = Nurse::on('tenant')
            ->where('user_id', $createdUser->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($nurse);

        $nurse->load('user');

        $this->assertNotNull($nurse->user);

        $this->assertSame('BNCLGU80A01H501Z', $nurse->personal_code);
        $this->assertSame('98765432109', $nurse->vat);
        $this->assertSame('00100', $nurse->cap);

        $this->assertSame('Luigi', $nurse->user->name);
        $this->assertSame('Bianchi', $nurse->user->surname);
        $this->assertSame($data['email'], $nurse->user->email);

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $nurse->user_id,
        ], 'tenant');

        Mail::assertSent(\App\Mail\PersonSetPasswordMail::class);
    }

    public function test_user_without_view_permission_cannot_view_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $nurse = $this->createNurse();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}")
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_view_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.view');

        $nurse = $this->createNurse();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}")
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_access_nurse_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $nurse = $this->createNurse();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}/edit")
            ->assertForbidden();
    }

    public function test_user_with_update_permission_can_access_nurse_edit_page(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.update');

        $nurse = $this->createNurse();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}/edit")
            ->assertSuccessful();
    }

    public function test_user_without_update_permission_cannot_update_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $nurse = $this->createNurse();

        $nationality = $this->createNationality();
        $data = $this->validNurseData($nationality->id);

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}", $data)
            ->assertForbidden();

        $nurse->refresh();
        $nurse->load('user');

        $this->assertSame('RSSMRA80A01H501Z', $nurse->personal_code);
        $this->assertSame('Mario', $nurse->user->name);
        $this->assertSame('Rossi', $nurse->user->surname);
    }

    public function test_user_with_update_permission_can_update_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.update');

        $nurse = $this->createNurse();

        $nationality = $this->createNationality();
        $data = $this->validNurseData($nationality->id);

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}", $data)
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $nurse->refresh();
        $nurse->load('user');

        $this->assertSame('BNCLGU80A01H501Z', $nurse->personal_code);
        $this->assertSame('98765432109', $nurse->vat);
        $this->assertSame('Luigi', $nurse->user->name);
        $this->assertSame('Bianchi', $nurse->user->surname);
        $this->assertSame($data['email'], $nurse->user->email);
        $this->assertSame('00100', $nurse->cap);
    }

    public function test_user_without_delete_permission_cannot_delete_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();
        $nurse = $this->createNurse();

        $userId = $nurse->user_id;

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('nurses', [
            'id' => $nurse->id,
        ], 'tenant');

        $this->assertDatabaseHas('users', [
            'id' => $userId,
        ], 'tenant');
    }

    public function test_user_with_delete_permission_can_delete_nurse(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.delete');

        $nurse = $this->createNurse();

        $nurseId = $nurse->id;
        $userId = $nurse->user_id;

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurseId}")
            ->assertRedirect();

        $this->assertDatabaseMissing('nurses', [
            'id' => $nurseId,
        ], 'tenant');

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ], 'tenant');
    }

    public function test_user_without_update_permission_cannot_send_nurse_reset_email(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUser();
        $nurse = $this->createNurse();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}/send-reset-email")
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_user_with_update_permission_can_send_nurse_reset_email(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->givePermissionTo('nurse.update');

        $nurse = $this->createNurse();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/nurses/{$nurse->id}/send-reset-email")
            ->assertRedirect();

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $nurse->user_id,
        ], 'tenant');

        Mail::assertSent(\App\Mail\PersonSetPasswordMail::class);
    }
}
