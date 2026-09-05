<?php

namespace Tests\Feature\Tenant;

use App\Mail\PersonSetPasswordMail;
use App\Models\Clinic;
use App\Models\Nationality;
use App\Models\Secretary;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SecretaryControllerTest extends TestCase
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
            'name' => 'secretary',
            'guard_name' => 'web',
        ]);

        foreach ([
            'secretary.view',
            'secretary.create',
            'secretary.update',
            'secretary.delete',
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

    private function createUserWithPermissions(array $permissions): User
    {
        $user = $this->createUser();

        $role = Role::on('tenant')
            ->where('name', 'secretary')
            ->where('guard_name', 'web')
            ->firstOrFail();

        $permissionModels = Permission::on('tenant')
            ->whereIn('name', $permissions)
            ->where('guard_name', 'web')
            ->get();

        $role->syncPermissions($permissionModels);

        $user->assignRole($role);

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

    private function createSecretary(): Secretary
    {
        $user = $this->createUser();

        $user->update([
            'name' => 'Mario',
            'surname' => 'Rossi',
        ]);

        $nationality = $this->createNationality();

        return Secretary::on('tenant')->create([
            'user_id' => $user->id,
            'personal_code' => 'RSSMRA80A01H501Z',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'genre' => 'M',
            'nationality_id' => $nationality->id,
            'employee_code' => 'SEC-0001',
            'notes' => 'Note di test',
            'zip_code' => '00100',
        ]);
    }

    private function validSecretaryData(?int $nationalityId = null): array
    {
        $nationalityId ??= $this->createNationality()->id;

        return [
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'email' => 'luigi.'.Str::lower(Str::random(8)).'@example.com',
            'personal_code' => 'BNCLGU80A01H501Z',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Milano 10',
            'phone' => '3339876543',
            'genre' => 'M',
            'nationality_id' => $nationalityId,
            'employee_code' => null,
            'notes' => 'Note segretaria',
            'zip_code' => '00100',
        ];
    }

    public function test_user_with_view_permission_can_view_secretaries(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.view']);

        Secretary::on('tenant')->delete();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries")
            ->assertOk();
    }

    public function test_user_without_view_permission_cannot_view_secretaries(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries")
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_view_create_form(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.create']);

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries/create")
            ->assertOk();
    }

    public function test_user_without_create_permission_cannot_view_create_form(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries/create")
            ->assertForbidden();
    }

    public function test_user_with_create_permission_can_create_secretary(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.create']);
        $nationality = $this->createNationality();

        $data = $this->validSecretaryData($nationality->id);

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/secretaries", $data)
            ->assertRedirect();

        $emailHash = hash(
            'sha256',
            mb_strtolower(trim($data['email']))
        );

        $createdUser = User::on('tenant')
            ->where('email_hash', $emailHash)
            ->first();

        $this->assertNotNull($createdUser);

        $secretary = Secretary::on('tenant')
            ->where('user_id', $createdUser->id)
            ->first();

        $this->assertNotNull($secretary);

        $this->assertSame($data['name'], $createdUser->name);
        $this->assertSame($data['surname'], $createdUser->surname);
        $this->assertSame($data['email'], $createdUser->email);

        $this->assertSame($data['birthday'], $secretary->birthday);
        $this->assertSame($data['birth_city'], $secretary->birth_city);
        $this->assertSame($data['city'], $secretary->city);
        $this->assertSame($data['address'], $secretary->address);
        $this->assertSame($data['phone'], $secretary->phone);
        $this->assertSame($data['genre'], $secretary->genre);

        $this->assertDatabaseHas('secretaries', [
            'user_id' => $createdUser->id,
            'nationality_id' => $nationality->id,
        ], 'tenant');

        $this->assertSame($data['notes'], $secretary->notes);
        $this->assertSame($data['zip_code'], $secretary->zip_code);

        $this->assertNotNull($secretary->employee_code);

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $createdUser->id,
        ], 'tenant');

        Mail::assertSent(PersonSetPasswordMail::class);
    }

    public function test_user_without_create_permission_cannot_create_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);
        $nationality = $this->createNationality();

        $data = $this->validSecretaryData($nationality->id);

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/secretaries", $data)
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_view_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.view']);
        $secretary = $this->createSecretary();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}")
            ->assertOk();
    }

    public function test_user_without_view_permission_cannot_view_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);
        $secretary = $this->createSecretary();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}")
            ->assertForbidden();
    }

    public function test_user_with_update_permission_can_view_edit_form(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.update']);
        $secretary = $this->createSecretary();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}/edit")
            ->assertOk();
    }

    public function test_user_without_update_permission_cannot_view_edit_form(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);
        $secretary = $this->createSecretary();

        $this->actingAs($user)
            ->get("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}/edit")
            ->assertForbidden();
    }

    public function test_user_with_update_permission_can_update_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.update']);
        $secretary = $this->createSecretary();

        $nationality = $this->createNationality();

        $data = [
            'name' => 'Giovanni',
            'surname' => 'Verdi',
            'email' => 'giovanni.'.Str::lower(Str::random(8)).'@example.com',
            'personal_code' => 'VRDGNN80A01H501Z',
            'birthday' => '1981-02-02',
            'birth_city' => 'Milano',
            'city' => 'Milano',
            'address' => 'Via Torino 20',
            'phone' => '3331112222',
            'genre' => 'M',
            'nationality_id' => $nationality->id,
            'employee_code' => $secretary->employee_code,
            'notes' => 'Note aggiornate',
            'zip_code' => '20100',
        ];

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}", $data)
            ->assertRedirect();

        $secretary->refresh();
        $secretary->load('user');

        $this->assertSame('Giovanni', $secretary->user->name);
        $this->assertSame('Verdi', $secretary->user->surname);
        $this->assertSame($data['email'], $secretary->user->email);

        $this->assertSame($data['personal_code'], $secretary->personal_code);
        $this->assertSame($data['birthday'], $secretary->birthday);
        $this->assertSame($data['birth_city'], $secretary->birth_city);
        $this->assertSame($data['city'], $secretary->city);
        $this->assertSame($data['address'], $secretary->address);
        $this->assertSame($data['phone'], $secretary->phone);
        $this->assertSame($data['genre'], $secretary->genre);
        $this->assertSame($data['nationality_id'], $secretary->nationality_id);
        $this->assertSame($data['notes'], $secretary->notes);
        $this->assertSame($data['zip_code'], $secretary->zip_code);
    }

    public function test_user_without_update_permission_cannot_update_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);
        $secretary = $this->createSecretary();

        $data = [
            'name' => 'Giovanni',
            'surname' => 'Verdi',
            'email' => 'giovanni@example.com',
            'personal_code' => 'VRDGNN80A01H501Z',
            'birthday' => '1981-02-02',
            'birth_city' => 'Milano',
            'city' => 'Milano',
            'address' => 'Via Torino 20',
            'phone' => '3331112222',
            'genre' => 'M',
            'nationality_id' => $secretary->nationality_id,
            'employee_code' => $secretary->employee_code,
            'notes' => 'Note aggiornate',
            'zip_code' => '20100',
        ];

        $this->actingAs($user)
            ->put("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}", $data)
            ->assertForbidden();
    }

    public function test_user_with_delete_permission_can_delete_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.delete']);
        $secretary = $this->createSecretary();

        $secretaryId = $secretary->id;
        $userId = $secretary->user_id;

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretaryId}")
            ->assertRedirect();

        $this->assertDatabaseMissing('secretaries', [
            'id' => $secretaryId,
        ], 'tenant');

        $this->assertDatabaseMissing('users', [
            'id' => $userId,
        ], 'tenant');
    }

    public function test_user_without_delete_permission_cannot_delete_secretary(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);
        $secretary = $this->createSecretary();

        $this->actingAs($user)
            ->delete("http://{$clinic->slug}.clinika.test/admin/secretaries/{$secretary->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('secretaries', [
            'id' => $secretary->id,
        ], 'tenant');
    }

    public function test_user_without_update_permission_cannot_send_secretary_reset_email(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions([]);
        $secretary = $this->createSecretary();

        Mail::fake();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/secretary/{$secretary->id}/send-reset-email")
            ->assertForbidden();

        Mail::assertNothingSent();
    }

    public function test_user_with_update_permission_can_send_secretary_reset_email(): void
    {
        Mail::fake();

        $clinic = $this->createClinic();
        $user = $this->createUserWithPermissions(['secretary.update']);
        $secretary = $this->createSecretary();

        $this->actingAs($user)
            ->post("http://{$clinic->slug}.clinika.test/admin/secretary/{$secretary->id}/send-reset-email")
            ->assertRedirect();

        $this->assertDatabaseHas('password_reset_tokens', [
            'user_id' => $secretary->user_id,
        ], 'tenant');

        Mail::assertSent(PersonSetPasswordMail::class, function ($mail) use ($secretary) {
            return $mail->hasTo($secretary->user->email);
        });
    }
}
