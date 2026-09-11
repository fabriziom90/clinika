<?php

namespace Tests\Feature\Tenant;

use App\Models\Patient;
use App\Models\PatientHealthHistory;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TenantTestCase;

class PatientHealthHistoryControllerTest extends TenantTestCase
{
    private function host(): string
    {
        return $this->clinic->slug.'.clinika.test';
    }

    private function url(string $routeName, array $params = []): string
    {
        return 'http://'.$this->host().route($routeName, $params, false);
    }

    private function validPayload(Patient $patient, array $overrides = []): array
    {
        return array_merge([
            'patient_id' => $patient->id,
            'change_reason' => 'Prima raccolta anamnesi',
            'allergies' => 'Nessuna nota',
            'chronic_diseases' => 'Ipertensione',
            'current_therapies' => 'Ramipril 5mg',
            'surgical_history' => null,
            'family_history' => 'Diabete materno',
            'lifestyle' => 'Non fumatore',
            'vaccinations' => 'Antinfluenzale 2025',
            'notes' => 'Paziente collaborativo',
        ], $overrides);
    }

    public function test_user_without_create_permission_cannot_add_health_history(): void
    {
        $user = User::factory()->create();
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->post($this->url('admin.patient-health-history.store'), $this->validPayload($patient))
            ->assertForbidden();

        $this->assertDatabaseMissing('patient_health_histories', ['patient_id' => $patient->id], 'tenant');
    }

    public function test_user_with_create_permission_can_add_health_history(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-health-history.create');
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->post($this->url('admin.patient-health-history.store'), $this->validPayload($patient))
            ->assertRedirect();

        $this->assertDatabaseHas('patient_health_histories', [
            'patient_id' => $patient->id,
            'version' => 1,
            'is_current' => 1,
        ], 'tenant');
    }

    public function test_new_version_deactivates_the_previous_current_version(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-health-history.create');
        $patient = Patient::factory()->create();

        // Prima versione
        $this->actingAs($user)
            ->post($this->url('admin.patient-health-history.store'), $this->validPayload($patient))
            ->assertRedirect();

        $firstVersion = PatientHealthHistory::where('patient_id', $patient->id)->first();
        $this->assertTrue((bool) $firstVersion->is_current);

        // Seconda versione, stesso paziente
        $this->actingAs($user)
            ->post($this->url('admin.patient-health-history.store'), $this->validPayload($patient, [
                'change_reason' => 'Aggiornamento dopo controllo',
                'chronic_diseases' => 'Ipertensione, Diabete tipo 2',
            ]))
            ->assertRedirect();

        $firstVersion->refresh();
        $this->assertFalse(
            (bool) $firstVersion->is_current,
            'La prima versione deve essere disattivata quando ne viene creata una nuova.'
        );

        $secondVersion = PatientHealthHistory::where('patient_id', $patient->id)
            ->where('is_current', true)
            ->first();

        $this->assertNotNull($secondVersion);
        $this->assertSame(2, $secondVersion->version);
        $this->assertSame('Ipertensione, Diabete tipo 2', $secondVersion->chronic_diseases);

        // Un solo record "corrente" per paziente, sempre.
        $this->assertSame(
            1,
            PatientHealthHistory::where('patient_id', $patient->id)->where('is_current', true)->count()
        );
    }

    public function test_health_history_sensitive_data_is_encrypted_at_rest(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-health-history.create');
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->post($this->url('admin.patient-health-history.store'), $this->validPayload($patient))
            ->assertRedirect();

        $history = PatientHealthHistory::where('patient_id', $patient->id)->first();

        $raw = DB::connection('tenant')
            ->table('patient_health_histories')
            ->where('id', $history->id)
            ->first();

        $this->assertStringNotContainsString('Ipertensione', $raw->chronic_diseases);
        $this->assertStringNotContainsString('Ramipril', $raw->current_therapies);
        $this->assertStringNotContainsString('Diabete materno', $raw->family_history);
    }

    public function test_health_history_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('patient-health-history.create');
        $patient = Patient::factory()->create();

        $this->actingAs($user)
            ->post($this->url('admin.patient-health-history.store'), $this->validPayload($patient))
            ->assertRedirect();

        $history = PatientHealthHistory::where('patient_id', $patient->id)->first();

        $this->assertSame('Ipertensione', $history->chronic_diseases);
        $this->assertSame('Ramipril 5mg', $history->current_therapies);
        $this->assertSame('Diabete materno', $history->family_history);
    }
}
