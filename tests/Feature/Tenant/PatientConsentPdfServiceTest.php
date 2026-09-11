<?php

namespace Tests\Feature\Tenant;

use App\Services\PatientConsentPdfService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PatientConsentPdfServiceTest extends TestCase
{
    public function test_store_saves_uploaded_pdf_in_patient_consents_directory(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent(
            'consenso.pdf',
            '%PDF-1.4 test consent pdf content'
        );

        $service = new PatientConsentPdfService;

        $path = $service->store($file);

        $this->assertStringStartsWith('patient_consents/', $path);
        $this->assertStringEndsWith('.pdf', $path);
        $this->assertTrue(Storage::disk('local')->exists($path));

        $this->assertSame(
            '%PDF-1.4 test consent pdf content',
            Storage::disk('local')->get($path)
        );
    }

    public function test_store_generates_uuid_filename(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent(
            'consenso.pdf',
            '%PDF-1.4 test consent pdf content'
        );

        $service = new PatientConsentPdfService;

        $path = $service->store($file);

        $filename = basename($path);
        $uuid = pathinfo($filename, PATHINFO_FILENAME);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f-]{36}$/i',
            $uuid
        );

        $this->assertSame('pdf', pathinfo($filename, PATHINFO_EXTENSION));
    }

    public function test_store_preserves_uploaded_file_extension(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent(
            'consenso.pdf',
            '%PDF-1.4 test consent pdf content'
        );

        $service = new PatientConsentPdfService;

        $path = $service->store($file);

        $this->assertSame(
            'pdf',
            pathinfo($path, PATHINFO_EXTENSION)
        );
    }
}
