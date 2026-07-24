<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;

class PatientConsentPdfService
{
    private const DIRECTORY = 'patient_consents';

    //generate pdf
    public function store(UploadedFile $file): string
    {

        $extension = $file->getClientOriginalExtension();

        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs(self::DIRECTORY, $filename, 'local');
    }
}
