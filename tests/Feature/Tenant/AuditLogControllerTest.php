<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit as OwenAudit;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuditLogControllerTest extends TestCase
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
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
    }

    public function test_user_without_admin_role_cannot_access_audit_logs(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $this->actingAs($user, 'web')
            ->get($this->url($clinic))
            ->assertForbidden();
    }

    public function test_admin_can_access_audit_logs(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->assignRole('admin');

        $this->actingAs($user, 'web')
            ->get($this->url($clinic))
            ->assertSuccessful();
    }

    public function test_admin_can_filter_audit_logs_by_event(): void
    {
        $clinic = $this->createClinic();
        $user = $this->createUser();

        $user->assignRole('admin');

        OwenAudit::on('tenant')->truncate();

        OwenAudit::on('tenant')->create([
            'user_type' => User::class,
            'user_id' => $user->id,
            'event' => 'created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
            'url' => $this->url($clinic),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'tags' => null,
        ]);

        OwenAudit::on('tenant')->create([
            'user_type' => User::class,
            'user_id' => $user->id,
            'event' => 'updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'old_values' => [],
            'new_values' => [],
            'url' => $this->url($clinic),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'PHPUnit',
            'tags' => null,
        ]);

        $response = $this->actingAs($user, 'web')
            ->get($this->url($clinic).'?search=created')
            ->assertSuccessful();

        $logs = $response->viewData('page')['props']['logs']['data'];

        $this->assertCount(1, $logs);
        $this->assertSame('created', $logs[0]['event']);
    }

    private function url(Clinic $clinic): string
    {
        return "http://{$clinic->slug}.clinika.test/admin/audit-logs";
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
}
