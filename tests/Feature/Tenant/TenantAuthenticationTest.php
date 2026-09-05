<?php

namespace Tests\Feature\Tenant;

use App\Models\Clinic;
use App\Models\User;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TenantAuthenticationTest extends TestCase
{
    protected array $tenantDatabases = [];

    protected function createTenantDatabase(): Clinic
    {
        $database = 'clinika_test_'.Str::lower(Str::random(12));

        $clinic = Clinic::factory()->create([
            'database' => $database,
            'db_host' => env('DB_HOST', '127.0.0.1'),
            'db_port' => env('DB_PORT', '3306'),
            'db_username' => env('DB_USERNAME', 'root'),
            'db_password' => env('DB_PASSWORD', ''),
        ]);

        $this->tenantDatabases[] = $database;

        $service = app(TenantDatabaseService::class);

        $service->createDatabase($clinic);
        $service->connect($clinic);

        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);

        return $clinic;
    }

    protected function connectToClinic(Clinic $clinic): void
    {
        app(TenantDatabaseService::class)->connect($clinic);
    }

    protected function authenticate(string $email, string $password): bool
    {
        $emailHash = hash(
            'sha256',
            mb_strtolower(trim($email))
        );

        return Auth::guard('web')->attempt([
            'email_hash' => $emailHash,
            'password' => $password,
        ]);
    }

    protected function logout(): void
    {
        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }
    }

    protected function tearDown(): void
    {
        $this->logout();

        foreach ($this->tenantDatabases as $database) {
            DB::connection('central')->statement(
                'DROP DATABASE IF EXISTS `'.str_replace('`', '``', $database).'`'
            );
        }

        parent::tearDown();
    }

    public function test_user_can_authenticate_only_against_the_current_tenant_database(): void
    {
        $clinicA = $this->createTenantDatabase();

        $userA = User::factory()->create([
            'email' => 'user-a@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->assertTrue(
            $this->authenticate('user-a@example.com', 'password')
        );

        $this->assertAuthenticated('web');
        $this->assertSame($userA->id, Auth::guard('web')->id());

        $this->logout();

        $clinicB = $this->createTenantDatabase();

        $this->assertFalse(
            $this->authenticate('user-a@example.com', 'password')
        );

        $this->assertGuest('web');

        $userB = User::factory()->create([
            'email' => 'user-b@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->assertTrue(
            $this->authenticate('user-b@example.com', 'password')
        );

        $this->assertAuthenticated('web');
        $this->assertSame($userB->id, Auth::guard('web')->id());

        $this->logout();

        $this->connectToClinic($clinicA);

        $this->assertTrue(
            $this->authenticate('user-a@example.com', 'password')
        );

        $this->assertAuthenticated('web');
        $this->assertSame($userA->id, Auth::guard('web')->id());

        $this->logout();

        $this->connectToClinic($clinicB);

        $userAEmailHash = hash(
            'sha256',
            mb_strtolower(trim('user-a@example.com'))
        );

        $this->assertDatabaseMissing('users', [
            'email_hash' => $userAEmailHash,
        ], 'tenant');
    }
}
