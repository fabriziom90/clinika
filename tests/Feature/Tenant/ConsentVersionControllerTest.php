<?php

namespace Tests\Feature\Tenant;

use App\Models\ConsentType;
use App\Models\ConsentVersion;
use App\Models\User;
use Tests\TenantTestCase;

class ConsentVersionControllerTest extends TenantTestCase
{
    private function host(): string
    {
        return $this->clinic->slug.'.clinika.test';
    }

    private function url(string $routeName, array $params = []): string
    {
        return 'http://'.$this->host().route($routeName, $params, false);
    }

    private function createConsentType(): ConsentType
    {
        return ConsentType::create([
            'code' => 'privacy',
            'name' => 'Consenso Privacy',
            'description' => 'Trattamento dati personali',
            'acquisition_method' => 'paper',
            'is_required' => true,
            'is_active' => true,
        ]);
    }

    private function createConsentVersion(ConsentType $consentType): ConsentVersion
    {
        return $consentType->versions()->create([
            'version' => 1,
            'content' => 'Testo del consenso.',
            'is_active' => true,
            'published_at' => now(),
        ]);
    }

    public function test_user_without_view_permission_cannot_list_consent_versions(): void
    {
        $user = User::factory()->create();
        $consentType = $this->createConsentType();

        $this->actingAs($user)
            ->get($this->url('admin.consent-types.consent-versions.index', ['consent_type' => $consentType->id]))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_list_consent_versions(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('consent-version.view');
        $consentType = $this->createConsentType();

        $this->actingAs($user)
            ->get($this->url('admin.consent-types.consent-versions.index', ['consent_type' => $consentType->id]))
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_a_consent_version(): void
    {
        $user = User::factory()->create();
        $consentType = $this->createConsentType();

        $this->actingAs($user)
            ->post($this->url('admin.consent-types.consent-versions.store', ['consent_type' => $consentType->id]), [
                'content' => 'Nuovo testo',
                'is_active' => true,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('consent_versions', ['content' => 'Nuovo testo'], 'tenant');
    }

    public function test_user_with_create_permission_can_create_a_consent_version(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('consent-version.create');
        $consentType = $this->createConsentType();

        $this->actingAs($user)
            ->post($this->url('admin.consent-types.consent-versions.store', ['consent_type' => $consentType->id]), [
                'content' => 'Nuovo testo',
                'is_active' => true,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('consent_versions', [
            'consent_type_id' => $consentType->id,
            'content' => 'Nuovo testo',
        ], 'tenant');
    }

    public function test_user_without_view_permission_cannot_show_a_consent_version(): void
    {
        $user = User::factory()->create();
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $this->actingAs($user)
            ->get($this->url('admin.consent-types.consent-versions.show', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]))
            ->assertForbidden();
    }

    public function test_user_without_delete_permission_cannot_delete_a_consent_version(): void
    {
        $user = User::factory()->create();
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $this->actingAs($user)
            ->delete($this->url('admin.consent-types.consent-versions.destroy', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas('consent_versions', ['id' => $consentVersion->id], 'tenant');
    }

    public function test_user_with_delete_permission_can_delete_an_unused_consent_version(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('consent-version.delete');
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $this->actingAs($user)
            ->delete($this->url('admin.consent-types.consent-versions.destroy', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]))
            ->assertRedirect();

        $this->assertSoftDeleted(
            'consent_versions',
            ['id' => $consentVersion->id],
            'tenant'
        );
    }

    public function test_user_without_view_permission_cannot_generate_pdf(): void
    {
        $user = User::factory()->create();
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $this->actingAs($user)
            ->get($this->url('admin.consent-types.consent-versions.generate-pdf', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_generate_pdf(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('consent-version.view');
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $this->actingAs($user)
            ->get($this->url('admin.consent-types.consent-versions.generate-pdf', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]))
            ->assertSuccessful();
    }

    /**
     * Una versione già usata per un consenso paziente non deve essere
     * cancellabile (regola di business già presente in destroy(), non
     * legata all'autorizzazione ma comunque utile da proteggere da
     * regressioni).
     */
    public function test_a_consent_version_already_used_by_a_patient_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('consent-version.delete');
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $patient = \App\Models\Patient::factory()->create();
        $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
        ]);

        $this->actingAs($user)
            ->delete($this->url('admin.consent-types.consent-versions.destroy', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]))
            ->assertRedirect();

        $this->assertDatabaseHas('consent_versions', ['id' => $consentVersion->id], 'tenant');
    }

    /**
     * edit() e update() nel controller sono corpi vuoti ("//"): non c'è
     * ancora una vera funzionalità di modifica da testare nel merito.
     * Verifichiamo comunque che il controllo di autorizzazione scatti prima
     * di arrivare al corpo vuoto del metodo.
     */
    public function test_user_without_update_permission_cannot_reach_update(): void
    {
        $user = User::factory()->create();
        $consentType = $this->createConsentType();
        $consentVersion = $this->createConsentVersion($consentType);

        $this->actingAs($user)
            ->put($this->url('admin.consent-types.consent-versions.update', [
                'consent_type' => $consentType->id,
                'consent_version' => $consentVersion->id,
            ]), [])
            ->assertForbidden();
    }
}
