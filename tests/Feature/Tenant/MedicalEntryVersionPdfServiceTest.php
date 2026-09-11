<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalEntry;
use App\Models\MedicalEntryVersion;
use App\Models\MedicalRecord;
use App\Models\Nationality;
use App\Models\Patient;
use App\Models\User;
use App\Services\MedicalEntryVersionPdfService;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Smalot\PdfParser\Parser;
use Tests\TestCase;

class MedicalEntryVersionPdfServiceTest extends TestCase
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

    public function test_generate_creates_medical_entry_pdf_in_expected_path(): void
    {
        $version = $this->createMedicalEntryVersion();

        $service = new MedicalEntryVersionPdfService;

        $path = $service->generate($version);

        $expectedPath = sprintf(
            'medical-entries/%s/mev-%s.pdf',
            $version->created_at->year,
            $version->uuid
        );

        $this->assertSame($expectedPath, $path);
        $this->assertTrue(Storage::exists($expectedPath));
        $this->assertStringStartsWith('%PDF', Storage::get($expectedPath));

        Storage::delete($expectedPath);
    }

    public function test_get_pdf_path_generates_and_saves_pdf_path_when_pdf_does_not_exist(): void
    {
        $version = $this->createMedicalEntryVersion();

        $service = new MedicalEntryVersionPdfService;

        $path = $service->getPdfPath($version);

        $expectedPath = sprintf(
            'medical-entries/%s/mev-%s.pdf',
            $version->created_at->year,
            $version->uuid
        );

        $this->assertSame($expectedPath, $path);
        $this->assertTrue(Storage::exists($expectedPath));

        $savedVersion = MedicalEntryVersion::on('tenant')
            ->findOrFail($version->id);

        $this->assertSame($expectedPath, $savedVersion->pdf_path);

        Storage::delete($expectedPath);
    }

    public function test_get_pdf_path_returns_existing_medical_entry_pdf_without_regenerating_it(): void
    {
        $version = $this->createMedicalEntryVersion();

        $existingPath = sprintf(
            'medical-entries/%s/existing-%s.pdf',
            $version->created_at->year,
            $version->uuid
        );

        $existingContent = '%PDF-existing-medical-entry-pdf';

        Storage::put($existingPath, $existingContent);

        $version->pdf_path = $existingPath;
        $version->save();

        $service = new MedicalEntryVersionPdfService;

        $path = $service->getPdfPath($version);

        $this->assertSame($existingPath, $path);
        $this->assertSame($existingContent, Storage::get($existingPath));

        Storage::delete($existingPath);
    }

    public function test_generated_pdf_contains_valid_pdf_data(): void
    {
        $version = $this->createMedicalEntryVersion();

        $service = new MedicalEntryVersionPdfService;

        $path = $service->generate($version);

        $content = Storage::get($path);

        $this->assertNotEmpty($content);
        $this->assertStringStartsWith('%PDF', $content);

        Storage::delete($path);
    }

    public function test_generated_pdf_contains_medical_entry_data(): void
    {
        $version = $this->createMedicalEntryVersion();

        $path = app(MedicalEntryVersionPdfService::class)->generate($version);

        $pdfPath = storage_path('app/'.$path);

        $this->assertFileExists($pdfPath);

        $parser = new Parser;
        $pdf = $parser->parseFile($pdfPath);
        $text = $pdf->getText();

        $this->assertStringContainsString('Referto Medico', $text);
        $this->assertStringContainsString('Mario Rossi', $text);
        $this->assertStringContainsString('Luca Bianchi', $text);
        $this->assertStringContainsString('Visita iniziale', $text);
        $this->assertStringContainsString('Visit', $text);
        $this->assertStringContainsString('Contenuto della visita', $text);

        Storage::delete($path);
    }

    private function createMedicalEntryVersion(): MedicalEntryVersion
    {
        $user = $this->createUser();
        $doctor = $this->createDoctor($user);
        $patient = $this->createPatient();

        $medicalRecord = MedicalRecord::on('tenant')
            ->where('patient_id', $patient->id)
            ->latest('id')
            ->firstOrFail();

        $appointment = new Appointment;
        $appointment->setConnection('tenant');
        $appointment->patient_id = $patient->id;
        $appointment->doctor_id = $doctor->id;
        $appointment->start_time = now()->addHour();
        $appointment->end_time = now()->addHours(2);
        $appointment->save();

        $entry = new MedicalEntry;
        $entry->setConnection('tenant');
        $entry->medical_record_id = $medicalRecord->id;
        $entry->appointment_id = $appointment->id;
        $entry->doctor_id = $doctor->id;
        $entry->cancelled_by = null;
        $entry->cancelled_at = null;
        $entry->save();

        $versionNumber = (
            MedicalEntryVersion::on('tenant')
                ->where('medical_entry_id', $entry->id)
                ->max('version') ?? 0
        ) + 1;

        $version = new MedicalEntryVersion;
        $version->setConnection('tenant');
        $version->uuid = (string) Str::uuid();
        $version->medical_entry_id = $entry->id;
        $version->version = $versionNumber;
        $version->type = 'visit';
        $version->title = 'Visita iniziale';
        $version->content = 'Contenuto della visita';
        $version->save();

        return $version->fresh();
    }

    private function createUser(): User
    {
        $user = User::factory()->make();

        $user->setConnection('tenant');
        $user->save();

        $user->update([
            'name' => 'Luca',
            'surname' => 'Bianchi',
        ]);

        return $user->fresh();
    }

    private function createNationality(): Nationality
    {
        $nationality = new Nationality;
        $nationality->setConnection('tenant');
        $nationality->name = 'Italiana '.Str::lower(Str::random(6));
        $nationality->state = 'Italia';
        $nationality->save();

        return $nationality;
    }

    private function createDoctor(User $user): Doctor
    {
        $nationality = $this->createNationality();

        return Doctor::on('tenant')->create([
            'user_id' => $user->id,
            'personal_code' => Str::upper(Str::random(16)),
            'vat' => (string) random_int(10000000000, 99999999999),
            'birthday' => '1980-01-01',
            'birth_city' => 'Roma',
            'city' => 'Roma',
            'address' => 'Via Roma 1',
            'phone' => '333'.random_int(1000000, 9999999),
            'genre' => 'M',
            'cap' => '00100',
            'pec' => Str::lower(Str::random(8)).'@pec.example.com',
            'nationality_id' => $nationality->id,
        ]);
    }

    private function createPatient(): Patient
    {
        $patient = new Patient;
        $patient->setConnection('tenant');
        $patient->name = 'Mario';
        $patient->surname = 'Rossi';
        $patient->email = 'patient-'.Str::lower(Str::random(10)).'@example.com';
        $patient->personal_code = Str::upper(Str::random(16));
        $patient->birthday = '1990-01-01';
        $patient->birth_city = 'Roma';
        $patient->city = 'Roma';
        $patient->address = 'Via Roma 1';
        $patient->zip_code = '00100';
        $patient->phone = '333'.random_int(1000000, 9999999);
        $patient->genre = 'M';
        $patient->save();

        return $patient;
    }
}
