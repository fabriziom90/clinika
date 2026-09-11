<?php

namespace Tests\Feature\Tenant;

use App\Models\ConsentType;
use App\Models\ConsentVersion;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Tests\TenantTestCase;

class PatientConsentControllerTest extends TenantTestCase
{
    private function host(): string
    {
        return $this->clinic->slug.'.clinika.test';
    }

    private function url(string $routeName, array $params = []): string
    {
        return 'http://'.$this->host().route($routeName, $params, false);
    }

    private function createConsentTypeWithActiveVersion(): array
    {
        $consentType = ConsentType::create([
            'code' => 'privacy',
            'name' => 'Consenso Privacy',
            'description' => 'Trattamento dati personali',
            'acquisition_method' => 'paper',
            'is_required' => true,
            'is_active' => true,
        ]);

        $consentVersion = ConsentVersion::create([
            'consent_type_id' => $consentType->id,
            'version' => 1,
            'content' => 'Testo del consenso, versione 1.',
            'is_active' => true,
            'published_at' => now(),
        ]);

        return [$consentType, $consentVersion];
    }

    private function validPayload(ConsentType $consentType, ConsentVersion $consentVersion): array
    {
        return [
            'consents' => [
                [
                    'consent_type_id' => $consentType->id,
                    'consent_version_id' => $consentVersion->id,
                    'status' => 'accepted',
                    'acquisition_method' => 'paper',
                ],
            ],
        ];
    }

    public function test_user_without_view_permission_cannot_access_patient_consents(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->get($this->url('admin.patient.consents.index', ['patient' => $patient->id]))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_patient_consents(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-consent.view');
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->get($this->url('admin.patient.consents.index', ['patient' => $patient->id]))
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_register_a_consent(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $this->actingAs($user)
            ->post(
                $this->url('admin.patient.consents.store', ['patient' => $patient->id]),
                $this->validPayload($consentType, $consentVersion)
            )
            ->assertForbidden();

        $this->assertDatabaseMissing(
            'patient_consents',
            ['patient_id' => $patient->id],
            'tenant'
        );
    }

    public function test_user_with_create_permission_can_register_a_consent(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-consent.create');
        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $this->actingAs($user)
            ->post(
                $this->url('admin.patient.consents.store', ['patient' => $patient->id]),
                $this->validPayload($consentType, $consentVersion)
            )
            ->assertRedirect();

        $this->assertDatabaseHas('patient_consents', [
            'patient_id' => $patient->id,
            'consent_type_id' => $consentType->id,
            'status' => 'accepted',
        ], 'tenant');
    }

    public function test_user_without_delete_permission_cannot_delete_a_consent(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $consent = $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
            'recorded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete($this->url('admin.patient.consents.destroy', [
                'patient' => $patient->id,
                'consent' => $consent->id,
            ]))
            ->assertForbidden();

        $this->assertDatabaseHas(
            'patient_consents',
            ['id' => $consent->id],
            'tenant'
        );
    }

    public function test_user_with_delete_permission_can_delete_a_consent(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-consent.delete');
        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $consent = $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
            'recorded_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->delete($this->url('admin.patient.consents.destroy', [
                'patient' => $patient->id,
                'consent' => $consent->id,
            ]))
            ->assertRedirect();

        $this->assertSoftDeleted(
            'patient_consents',
            ['id' => $consent->id],
            connection: 'tenant'
        );
    }

    public function test_user_without_view_permission_cannot_access_consent_document(): void
    {
        Storage::disk('local')->put(
            'patient_consents/test-consent.pdf',
            '%PDF-1.4 test consent pdf'
        );

        $user = User::factory()->create();
        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $consent = $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
            'recorded_by' => $user->id,
            'pdf_path' => 'patient_consents/test-consent.pdf',
        ]);

        $this->actingAs($user)
            ->get($this->url('admin.patient.consents.document', [
                'patient' => $patient->id,
                'consent' => $consent->id,
            ]))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_consent_document_and_audit_is_created(): void
    {
        Storage::disk('local')->put(
            'patient_consents/test-consent.pdf',
            '%PDF-1.4 test consent pdf'
        );

        $user = User::factory()->create();
        $user->givePermissionTo('patient-consent.view');

        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $consent = $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
            'recorded_by' => $user->id,
            'pdf_path' => 'patient_consents/test-consent.pdf',
        ]);

        $response = $this->actingAs($user)->get($this->url('admin.patient.consents.document', ['patient' => $patient->id, 'consent' => $consent->id]));

        $response->assertSuccessful()->assertHeader('content-type', 'application/pdf');

        $this->assertSame(
            '%PDF-1.4 test consent pdf',
            file_get_contents($response->getFile()->getPathname())
        );

        $this->assertDatabaseHas('audits', ['user_id' => $user->id, 'event' => 'viewed consent document', 'auditable_type' => 'App\Models\PatientConsent', 'auditable_id' => $consent->id], 'tenant');
    }

    public function test_consent_document_returns_not_found_when_patient_does_not_match_consent(): void
    {
        Storage::disk('local')->put(
            'patient_consents/test-consent.pdf',
            '%PDF-1.4 test consent pdf'
        );

        $user = User::factory()->create();
        $user->givePermissionTo('patient-consent.view');

        $patient = Patient::factory()->create();
        $otherPatient = Patient::factory()->create();

        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $consent = $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
            'recorded_by' => $user->id,
            'pdf_path' => 'patient_consents/test-consent.pdf',
        ]);

        $this->actingAs($user)
            ->get($this->url('admin.patient.consents.document', [
                'patient' => $otherPatient->id,
                'consent' => $consent->id,
            ]))
            ->assertNotFound();
    }

    public function test_consent_document_returns_not_found_when_pdf_file_does_not_exist(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-consent.view');

        $patient = Patient::factory()->create();
        [$consentType, $consentVersion] = $this->createConsentTypeWithActiveVersion();

        $consent = $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
            'recorded_by' => $user->id,
            'pdf_path' => 'patient_consents/non-existent.pdf',
        ]);

        $this->actingAs($user)
            ->get($this->url('admin.patient.consents.document', [
                'patient' => $patient->id,
                'consent' => $consent->id,
            ]))
            ->assertNotFound();
    }
}
