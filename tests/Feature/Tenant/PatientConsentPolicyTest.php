<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\ConsentType;
use App\Models\ConsentVersion;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientConsent;
use App\Models\User;
use Tests\TenantTestCase;

/**
 * Verifica puntuale di PatientConsentPolicy::view(), il metodo con la logica
 * più delicata (dipende dal ruolo, non solo dal permesso "flat").
 *
 * IMPORTANTE: questi test richiedono la versione CORRETTA di
 * PatientConsentPolicy (vedi app/Policies/PatientConsentPolicy.php fornita
 * a parte). Con la versione originale del codice, "un medico con un
 * appuntamento verso il paziente può vedere il consenso" va in errore fatale
 * (BadMethodCallException: Patient::doctors() non esiste) invece di passare.
 */
class PatientConsentPolicyTest extends TenantTestCase
{
    private function makeConsent(Patient $patient): PatientConsent
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
            'content' => 'Testo del consenso.',
            'is_active' => true,
            'published_at' => now(),
        ]);

        return $patient->consents()->create([
            'consent_type_id' => $consentType->id,
            'consent_version_id' => $consentVersion->id,
            'status' => 'accepted',
            'acquisition_method' => 'paper',
        ]);
    }

    public function test_admin_can_view_any_patient_consent(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $patient = Patient::factory()->create();
        $consent = $this->makeConsent($patient);

        $this->assertTrue($admin->can('view', $consent));
    }

    public function test_secretary_with_permission_can_view_any_patient_consent(): void
    {
        $secretary = User::factory()->create();
        $secretary->assignRole('secretary'); // il ruolo secretary ha già patient-consent.view da RoleSeeder

        $patient = Patient::factory()->create();
        $consent = $this->makeConsent($patient);

        $this->assertTrue($secretary->can('view', $consent));
    }

    public function test_doctor_with_an_appointment_for_the_patient_can_view_the_consent(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor'); // il ruolo doctor ha già patient-consent.view da RoleSeeder
        $doctor = Doctor::factory()->create(['user_id' => $doctorUser->id]);

        $patient = Patient::factory()->create();
        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        $consent = $this->makeConsent($patient);

        $this->assertTrue(
            $doctorUser->can('view', $consent),
            'Un medico con un appuntamento verso questo paziente deve poter vedere il consenso. '
            .'Se questo fallisce con BadMethodCallException, la Policy in uso è ancora quella '
            .'originale (Patient::doctors() non esiste).'
        );
    }

    public function test_doctor_can_view_consent_of_any_patient_not_just_his_own(): void
    {
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor'); // richiede patient-consent.view in RoleSeeder
        Doctor::factory()->create(['user_id' => $doctorUser->id]);

        // Paziente SENZA alcun appuntamento con questo medico: deve vedere
        // comunque, perché la regola confermata è "permesso piatto", non
        // "deve avere un legame diretto con il paziente".
        $patient = Patient::factory()->create();
        $consent = $this->makeConsent($patient);

        $this->assertTrue(
            $doctorUser->can('view', $consent),
            'Con la regola confermata, un medico con patient-consent.view vede '
            .'qualunque paziente della clinica, non solo i propri.'
        );
    }

    public function test_user_without_patient_consent_view_permission_cannot_view_it_even_as_admin_role_name_typo(): void
    {
        // Utente senza alcun ruolo/permesso: deve essere negato a prescindere.
        $user = User::factory()->create();

        $patient = Patient::factory()->create();
        $consent = $this->makeConsent($patient);

        $this->assertFalse($user->can('view', $consent));
    }
}
