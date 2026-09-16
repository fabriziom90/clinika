<?php

namespace Tests\Feature\Tenant;

use App\Enums\AppointmentStatus;
use App\Enums\ReminderStatus;
use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\ReminderType;
use App\Services\AppointmentReminderService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AppointmentReminderTest extends TestCase
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

    private function createPatient(): Patient
    {
        return Patient::create([
            'name' => 'Mario',
            'surname' => 'Rossi',
            'personal_code' => 'RSSMRA80A01H501Z',
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '3331234567',
            'email' => 'mario.rossi@example.com',
            'genre' => 'M',
            'zip_code' => '00100',
        ]);
    }

    private function createAppointment(Patient $patient): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $patient->id,
            'start_time' => '2026-08-27 10:00:00',
            'status' => AppointmentStatus::Scheduled,
            'notes' => 'Visita di controllo',
        ]);

        $appointment->end_time = '2026-08-27 10:30:00';
        $appointment->duration_minutes = 30;
        $appointment->save();

        return $appointment;
    }

    private function createReminderType(): ReminderType
    {
        return ReminderType::create([
            'name' => 'Scadenza',
            'code' => 'test_reminder',
            'subject' => 'Promemoria appuntamento',
            'message' => 'Questo è un promemoria di test.',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => true,
        ]);
    }

    private function createReminder(Appointment $appointment, Patient $patient): AppointmentReminder
    {
        $reminderType = $this->createReminderType();

        return AppointmentReminder::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
            'scheduled_for' => '2026-08-27 09:00:00',
            'status' => ReminderStatus::PENDING,
            'error_message' => null,
        ]);
    }

    private function createReminderWithService(?string $preparationInstructions, ?bool $withService = true): AppointmentReminder
    {
        $patient = Patient::factory()->create();
        $doctor = Doctor::factory()->create();

        $service = null;
        if ($withService) {
            $service = \App\Models\Service::create([
                'name' => 'Prelievo del sangue',
                'default_duration' => 15,
                'default_price' => 20,
                'preparation_instructions' => $preparationInstructions,
                'active' => true,
            ]);
        }

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
        ]);

        if ($service) {
            $appointment->service_id = $service->id;
            $appointment->save();
        }

        $reminderType = ReminderType::create([
            'name' => 'Promemoria test',
            'code' => 'test-'.uniqid(),
            'message' => 'Ti aspettiamo per il tuo appuntamento.',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => true,
        ]);

        return AppointmentReminder::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
            'scheduled_for' => now()->addDay(),
            'status' => ReminderStatus::PENDING,
        ]);
    }

    public function test_composed_message_includes_preparation_instructions_when_present(): void
    {
        $reminder = $this->createReminderWithService('Presentarsi a digiuno da almeno 8 ore.');

        $expected = "Ti aspettiamo per il tuo appuntamento.\n\nPresentarsi a digiuno da almeno 8 ore.";

        $this->assertSame($expected, $reminder->fresh()->composedMessage());
    }

    public function test_composed_message_is_just_the_standard_message_when_service_has_no_instructions(): void
    {
        $reminder = $this->createReminderWithService(null);

        $this->assertSame(
            'Ti aspettiamo per il tuo appuntamento.',
            $reminder->fresh()->composedMessage()
        );
    }

    public function test_composed_message_is_just_the_standard_message_when_service_instructions_are_empty_string(): void
    {
        $reminder = $this->createReminderWithService('');

        $this->assertSame(
            'Ti aspettiamo per il tuo appuntamento.',
            $reminder->fresh()->composedMessage()
        );
    }

    public function test_composed_message_is_just_the_standard_message_when_appointment_has_no_service(): void
    {
        $reminder = $this->createReminderWithService(null, withService: false);

        $this->assertSame(
            'Ti aspettiamo per il tuo appuntamento.',
            $reminder->fresh()->composedMessage()
        );
    }

    public function test_reminder_is_created_in_tenant_database(): void
    {
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($patient);

        $reminder = $this->createReminder($appointment, $patient);

        $this->assertSame('tenant', $reminder->getConnectionName());

        $this->assertDatabaseHas('appointment_reminders', [
            'id' => $reminder->id,
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'status' => 'pending',
        ], 'tenant');
    }

    public function test_reminder_status_is_cast_to_enum(): void
    {
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($patient);

        $reminder = $this->createReminder($appointment, $patient);

        $this->assertInstanceOf(ReminderStatus::class, $reminder->status);
        $this->assertSame(ReminderStatus::PENDING, $reminder->status);
    }

    public function test_reminder_dates_are_cast_to_carbon(): void
    {
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($patient);

        $reminder = $this->createReminder($appointment, $patient);

        $this->assertInstanceOf(Carbon::class, $reminder->scheduled_for);
        $this->assertNull($reminder->sent_at);

        $reminder->sent_at = '2026-08-27 09:05:00';
        $reminder->save();
        $reminder->refresh();

        $this->assertInstanceOf(Carbon::class, $reminder->sent_at);
    }

    public function test_reminder_belongs_to_appointment(): void
    {
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($patient);

        $reminder = $this->createReminder($appointment, $patient);

        $this->assertInstanceOf(Appointment::class, $reminder->appointment);
        $this->assertSame($appointment->id, $reminder->appointment->id);
        $this->assertSame('tenant', $reminder->appointment->getConnectionName());
    }

    public function test_reminder_belongs_to_patient(): void
    {
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($patient);

        $reminder = $this->createReminder($appointment, $patient);

        $this->assertInstanceOf(Patient::class, $reminder->patient);
        $this->assertSame($patient->id, $reminder->patient->id);
        $this->assertSame('tenant', $reminder->patient->getConnectionName());
    }

    /**
     * Questo test è scritto apposta per fallire con il codice attuale.
     *
     * app/Services/AppointmentReminderService.php, calculateScheduledDate():
     *   // return $date->setTime(8, 0);
     *   return now(); // only for testing
     *
     * Il calcolo corretto (appuntamento meno sent_before_value/sent_before_unit)
     * è presente ma commentato: il metodo restituisce sempre l'istante corrente,
     * indipendentemente dalla configurazione del tipo di promemoria.
     *
     * Non modificare questo test per farlo passare finché il bug non è corretto
     * rimuovendo quella riga e riattivando il calcolo reale.
     */
    public function test_reminder_is_scheduled_the_configured_time_before_the_appointment(): void
    {
        $patient = $this->createPatient();
        $appointment = $this->createAppointment($patient);

        // "1 giorno prima" — coerente con createReminderType(), ma lo rendo
        // esplicito qui perché è il valore su cui si basa l'assert.
        $reminderType = ReminderType::create([
            'name' => 'Promemoria 1 giorno prima',
            'code' => 'one_day_before',
            'subject' => 'Promemoria appuntamento',
            'message' => 'Ti aspettiamo domani.',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => true,
        ]);

        // Il servizio genera un reminder solo per i tipi che il paziente ha
        // tra le proprie preferenze attive (reminder_type_preferences).
        $patient->reminderTypes()->attach($reminderType->id);

        app(AppointmentReminderService::class)->generate($appointment);

        $reminder = $appointment->appointmentReminder()
            ->where('reminder_type_id', $reminderType->id)
            ->first();

        $this->assertNotNull($reminder, 'Il reminder deve essere stato creato da generate().');

        // Appuntamento: 2026-08-27 10:00:00, "1 giorno prima" => 2026-08-26 10:00:00
        $expected = Carbon::parse($appointment->start_time)->subDays(1);

        $this->assertTrue(
            $expected->isSameDay($reminder->scheduled_for),
            'Il reminder doveva essere schedulato per '.$expected->toDateString()
            .' (1 giorno prima dell\'appuntamento), ma risulta schedulato per '
            .$reminder->scheduled_for->toDateString().'. '
            .'Verifica calculateScheduledDate() in AppointmentReminderService: '
            .'oggi restituisce sempre now() invece di calcolare la data reale.'
        );
    }
}
