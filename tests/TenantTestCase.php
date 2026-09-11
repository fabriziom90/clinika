<?php

namespace Tests;

use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * TestCase base per QUALUNQUE test sotto Tests\Feature\Tenant\* (pazienti,
 * appuntamenti, fatture, cartelle cliniche, consensi...).
 *
 * REQUISITO UNICO, DA FARE UNA SOLA VOLTA prima di lanciare i test:
 *   php artisan migrate --database=central --path=database/migrations/core
 * (punta al DB indicato da DB_CENTRAL_* nel tuo .env.testing)
 *
 * Perché questa classe esiste e cosa fa, in breve:
 *
 * - Crea una Clinic finta nel DB central per ogni test.
 * - Chiama TenantDatabaseService::createDatabase() + connect(): SONO QUESTI
 *   DUE METODI a impostare a runtime host/porta/utente/password della
 *   connessione "tenant". Senza passare da qui, la connessione "tenant" non
 *   ha credenziali valide -> "Access denied for user ''@'localhost'".
 * - Lancia le migration tenant sul database fisico appena creato.
 * - Semina ruoli/permessi (RoleSeeder).
 * - Espone $this->clinic, popolata in setUp().
 *
 * QUALUNQUE test class che estenda questa classe deve dichiararlo con
 * "extends TenantTestCase", NON "extends TestCase" - altrimenti nessuno di
 * questi passaggi viene eseguito.
 *
 * Il database central NON viene ricreato (niente migrate:fresh) dentro i
 * test, per due motivi:
 * 1) Le migration core e tenant contengono entrambe una classe
 *    "CreateAuditsTable" con lo stesso nome: lanciarle nello stesso processo
 *    PHP causa un fatal error di PHP ("Cannot declare class ... already in
 *    use"). In produzione non succede perché migrate:core e migrate:tenant
 *    girano sempre in processi separati.
 * 2) TenantDatabaseService::createDatabase() esegue un CREATE DATABASE sulla
 *    connessione "central": in MySQL un DDL causa un commit implicito, che
 *    romperebbe qualunque transazione di test aperta su quella connessione.
 *    Per questo la pulizia qui è fatta a mano in tearDown(), non con un
 *    trait di transazione automatica.
 */
abstract class TenantTestCase extends TestCase
{
    protected Clinic $clinic;

    /** Cliniche create durante il test, per poterle ripulire in tearDown(). */
    private array $createdClinics = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->clinic = $this->makeClinic();
    }

    protected function tearDown(): void
    {
        foreach ($this->createdClinics as $clinic) {
            DB::connection('central')->statement(
                "DROP DATABASE IF EXISTS `{$clinic->database}`"
            );
        }

        foreach ($this->createdClinics as $clinic) {
            Clinic::on('central')->withTrashed()->find($clinic->id)?->forceDelete();
        }

        $this->createdClinics = [];

        parent::tearDown();
    }

    /**
     * Crea una seconda clinica isolata, utile per i test di isolamento
     * multi-tenant (creare dati su una clinica e verificare che non siano
     * visibili sull'altra).
     */
    protected function createAnotherClinic(): Clinic
    {
        return $this->makeClinic();
    }

    private function makeClinic(): Clinic
    {
        $clinic = Clinic::on('central')->create([
            'uuid' => (string) Str::uuid(),
            'name' => 'Clinica di Test '.Str::random(4),
            'slug' => 'test-'.Str::random(8),
            'database' => 'clinika_test_'.Str::random(8),
            'db_host' => env('DB_CENTRAL_HOST', '127.0.0.1'),
            'db_port' => env('DB_CENTRAL_PORT', '3306'),
            'db_username' => env('DB_CENTRAL_USERNAME'),
            'db_password' => env('DB_CENTRAL_PASSWORD'),
            'active' => true,
        ]);

        $this->createdClinics[] = $clinic;

        $tenantDb = app(TenantDatabaseService::class);
        $tenantDb->createDatabase($clinic);
        $tenantDb->connect($clinic);

        Artisan::call('migrate:fresh', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $this->seed(\Database\Seeders\RoleSeeder::class);

        return $clinic;
    }
}
