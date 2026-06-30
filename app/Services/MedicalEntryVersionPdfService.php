<?php

namespace App\Services;

use App\Models\MedicalEntryVersion;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class MedicalEntryVersionPdfService
{
    public function getPdfPath(MedicalEntryVersion $version): string
    {

        if (
            $version->pdf_path &&
            Storage::exists($version->pdf_path)
        ) {
            return $version->pdf_path;
        }

        $path = $this->generate($version);

        $version->update([
            'pdf_path' => $path,
        ]);

        return $path;
    }

    public function generate(MedicalEntryVersion $version): string
    {
        $version->load([
            'medicalEntry.appointment.patient',
            'medicalEntry.doctor.user',
            'attachments',
            'prescriptions',
            'vitalParameters',
        ]);

        $pdf = Pdf::loadView('pdf.medical_entry', [
            'entry' => $version->medicalEntry,
            'version' => $version,
        ]);

        $year = $version->created_at->year;

        $directory = "medical-entries/{$year}";

        if (! Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }

        $filename = sprintf(
            'mev-%s.pdf',
            $version->uuid
        );

        $path = $directory.'/'.$filename;

        Storage::put(
            $path,
            $pdf->output()
        );

        return $path;
    }
}
