<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Nationality;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DoctorTest extends TestCase
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
    }

    private function createUser(): User
    {
        $user = new User;

        $user->forceFill([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'email' => 'mario.rossi@example.com',
            'password' => Hash::make('password'),
        ]);

        $user->save();

        return $user;
    }

    private function createNationality(): Nationality
    {
        $id = DB::connection('tenant')->table('nationalities')->insertGetId([
            'name' => 'Italiana',
            'state' => 'Italia',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return Nationality::findOrFail($id);
    }

    private function createSpecialty(): Specialty
    {
        return Specialty::create([
            'name' => 'Cardiologia',
        ]);
    }

    private function createDoctor(?User $user = null, ?Nationality $nationality = null, ?Specialty $specialty = null): Doctor
    {
        return Doctor::create([
            'user_id' => $user?->id,
            'specialty_id' => $specialty?->id,
            'nationality_id' => $nationality?->id,
            'personal_code' => 'RSSMRA75A01H501X',
            'vat' => '12345678901',
            'birthday' => '1975-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Medica 1',
            'phone' => '3339876543',
            'pec' => 'mario.rossi@pec.example.com',
            'cap' => '00100',
            'genre' => 'M',
        ]);
    }

    private function createPatient(): Patient
    {
        return Patient::create([
            'name' => 'Luigi',
            'surname' => 'Bianchi',
            'personal_code' => 'BNCLGU80A01H501Z',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'email' => 'luigi.bianchi@example.com',
            'genre' => 'M',
            'zip_code' => '00100',
        ]);
    }

    private function createAppointment(Doctor $doctor, Patient $patient): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => AppointmentStatus::Scheduled,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    private function createService(): Service
    {
        return Service::create([
            'name' => 'Visita specialistica',
            'default_price' => 100,
            'default_duration' => 30,
            'active' => true,
        ]);
    }

    public function test_doctor_is_created_in_tenant_database(): void
    {
        $doctor = $this->createDoctor();

        $this->assertSame('tenant', $doctor->getConnectionName());

        $this->assertDatabaseHas('doctors', [
            'id' => $doctor->id,
        ], 'tenant');
    }

    public function test_doctor_sensitive_data_is_encrypted_at_rest(): void
    {
        $doctor = $this->createDoctor();

        $raw = DB::connection('tenant')
            ->table('doctors')
            ->where('id', $doctor->id)
            ->first();

        $this->assertNotSame('RSSMRA75A01H501X', $raw->personal_code);
        $this->assertNotSame('12345678901', $raw->vat);
        $this->assertNotSame('1975-01-01', $raw->birthday);
        $this->assertNotSame('Roma', $raw->city);
        $this->assertNotSame('Via Medica 1', $raw->address);
        $this->assertNotSame('3339876543', $raw->phone);
        $this->assertNotSame('M', $raw->genre);
    }

    public function test_doctor_sensitive_data_is_decrypted_when_retrieved_through_eloquent(): void
    {
        $doctor = $this->createDoctor();

        $doctor->refresh();

        $this->assertSame('RSSMRA75A01H501X', $doctor->personal_code);
        $this->assertSame('12345678901', $doctor->vat);
        $this->assertSame('1975-01-01', $doctor->birthday);
        $this->assertSame('Roma', $doctor->city);
        $this->assertSame('Via Medica 1', $doctor->address);
        $this->assertSame('3339876543', $doctor->phone);
        $this->assertSame('M', $doctor->genre);
    }

    public function test_doctor_belongs_to_user(): void
    {
        $user = $this->createUser();
        $doctor = $this->createDoctor($user);

        $this->assertInstanceOf(User::class, $doctor->user);
        $this->assertSame($user->id, $doctor->user->id);
        $this->assertSame('tenant', $doctor->user->getConnectionName());
    }

    public function test_doctor_belongs_to_nationality(): void
    {
        $nationality = $this->createNationality();
        $doctor = $this->createDoctor(null, $nationality);

        $this->assertInstanceOf(Nationality::class, $doctor->nationality);
        $this->assertSame($nationality->id, $doctor->nationality->id);
        $this->assertSame('tenant', $doctor->nationality->getConnectionName());
    }

    public function test_doctor_belongs_to_specialty(): void
    {
        $specialty = $this->createSpecialty();
        $doctor = $this->createDoctor(null, null, $specialty);

        $this->assertInstanceOf(Specialty::class, $doctor->specialty);
        $this->assertSame($specialty->id, $doctor->specialty->id);
        $this->assertSame('tenant', $doctor->specialty->getConnectionName());
    }

    public function test_doctor_has_appointments(): void
    {
        $doctor = $this->createDoctor();
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($doctor, $patient);

        $doctor->load('appointments');

        $this->assertCount(1, $doctor->appointments);
        $this->assertInstanceOf(Appointment::class, $doctor->appointments->first());
        $this->assertSame($appointment->id, $doctor->appointments->first()->id);
        $this->assertSame('tenant', $doctor->appointments->first()->getConnectionName());
    }

    public function test_doctor_has_services_with_pivot_data(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService();

        $doctor->services()->attach($service->id, [
            'price' => 120,
            'duration_minutes' => 45,
            'active' => true,
        ]);

        $doctor->load('services');

        $this->assertCount(1, $doctor->services);
        $this->assertSame($service->id, $doctor->services->first()->id);
        $this->assertSame(120.0, (float) $doctor->services->first()->pivot->price);
        $this->assertSame(45, $doctor->services->first()->pivot->duration_minutes);
        $this->assertTrue((bool) $doctor->services->first()->pivot->active);
    }

    public function test_service_belongs_to_doctor_through_pivot(): void
    {
        $doctor = $this->createDoctor();
        $service = $this->createService();

        $service->doctors()->attach($doctor->id, [
            'price' => 150,
            'duration_minutes' => 60,
            'active' => true,
        ]);

        $service->load('doctors');

        $this->assertCount(1, $service->doctors);
        $this->assertSame($doctor->id, $service->doctors->first()->id);
        $this->assertSame(150.0, (float) $service->doctors->first()->pivot->price);
        $this->assertSame(60, $service->doctors->first()->pivot->duration_minutes);
        $this->assertTrue((bool) $service->doctors->first()->pivot->active);
    }
}
