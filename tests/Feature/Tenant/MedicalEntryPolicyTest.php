<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class MedicalEntryPolicyTest extends TestCase
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

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'doctor', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'nurse', 'guard_name' => 'web']);
    }

    private function createEntry(?int $doctorId = null, ?int $patientId = null): MedicalEntry
    {
        $patient = $patientId
            ? Patient::findOrFail($patientId)
            : Patient::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctorId ?? Doctor::factory()->create()->id,
        ]);

        return MedicalEntry::create([
            'medical_record_id' => $patient->medicalRecord->id,
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctorId ?? $appointment->doctor_id,
        ]);
    }

    public function test_admin_can_view_any_medical_entry(): void
    {
        $entry = $this->createEntry();

        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->assertTrue($admin->can('view', $entry));
    }

    public function test_doctor_can_view_medical_entry_created_by_him(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');
        $doctor->user->setRelation('doctor', $doctor);

        $entry = $this->createEntry($doctor->id);

        $this->assertTrue($doctor->user->can('view', $entry));
    }

    public function test_doctor_can_view_medical_entry_of_patient_with_whom_he_has_an_appointment(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');
        $doctor->user->setRelation('doctor', $doctor);

        $patient = Patient::factory()->create();

        $entry = $this->createEntry(
            Doctor::factory()->create()->id,
            $patient->id
        );

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
        ]);

        $this->assertTrue($doctor->user->can('view', $entry));
    }

    public function test_doctor_cannot_view_medical_entry_of_unrelated_patient(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');

        $patient = Patient::factory()->create();

        $entry = $this->createEntry(
            Doctor::factory()->create()->id,
            $patient->id
        );

        $this->assertFalse($doctor->user->can('view', $entry));
    }

    public function test_nurse_can_view_medical_entry_of_patient_with_whom_she_has_an_appointment(): void
    {
        $nurse = Nurse::factory()->create();
        $user = User::findOrFail($nurse->user_id);
        $user->setRelation('doctor', null);
        $user->setRelation('nurse', $nurse);
        $user->assignRole('nurse');
        $patient = Patient::factory()->create();
        $entry = $this->createEntry();
        Appointment::factory()->create(['nurse_id' => $nurse->id, 'patient_id' => $patient->id, 'doctor_id' => $entry->appointment->doctor_id]);
        $entry->update(['medical_record_id' => $patient->medicalRecord->id]);
        $entry->refresh();
        $this->assertTrue($user->can('view', $entry));
    }

    public function test_nurse_cannot_view_medical_entry_of_unrelated_patient(): void
    {
        $nurse = Nurse::factory()->create();
        $nurse->user->assignRole('nurse');

        $entry = $this->createEntry();

        $this->assertFalse($nurse->user->can('view', $entry));
    }

    public function test_user_without_doctor_or_nurse_cannot_view_medical_entry(): void
    {
        $entry = $this->createEntry();

        $user = User::factory()->create();

        $this->assertFalse($user->can('view', $entry));
    }

    public function test_doctor_can_create_medical_entry(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');

        $this->assertTrue($doctor->user->can('create', MedicalEntry::class));
    }

    public function test_nurse_cannot_create_medical_entry(): void
    {
        $nurse = Nurse::factory()->create();
        $nurse->user->syncRoles(['nurse']);
        $nurse->user->setRelation('doctor', null);

        $this->assertFalse($nurse->user->can('create', MedicalEntry::class));
    }

    public function test_doctor_can_update_medical_entry_created_by_him(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');
        $doctor->user->setRelation('doctor', $doctor);

        $entry = $this->createEntry($doctor->id);

        $this->assertTrue($doctor->user->can('update', $entry));
    }

    public function test_doctor_cannot_update_medical_entry_created_by_another_doctor(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');

        $otherDoctor = Doctor::factory()->create();

        $entry = $this->createEntry($otherDoctor->id);

        $this->assertFalse($doctor->user->can('update', $entry));
    }

    public function test_nurse_cannot_update_medical_entry(): void
    {
        $nurse = Nurse::factory()->create();
        $nurse->user->assignRole('nurse');

        $doctor = Doctor::factory()->create();

        $entry = $this->createEntry($doctor->id);

        $this->assertFalse($nurse->user->can('update', $entry));
    }

    public function test_admin_can_delete_medical_entry(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $entry = $this->createEntry();

        $this->assertTrue($admin->can('delete', $entry));
    }

    public function test_doctor_cannot_delete_medical_entry(): void
    {
        $doctor = Doctor::factory()->create();
        $doctor->user->assignRole('doctor');

        $entry = $this->createEntry($doctor->id);

        $this->assertFalse($doctor->user->can('delete', $entry));
    }

    public function test_nurse_cannot_delete_medical_entry(): void
    {
        $nurse = Nurse::factory()->create();
        $nurse->user->assignRole('nurse');

        $entry = $this->createEntry();

        $this->assertFalse($nurse->user->can('delete', $entry));
    }
}
