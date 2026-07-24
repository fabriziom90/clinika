<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientConsentRequest;
use App\Http\Requests\UpdatePatientConsentRequest;
use App\Models\ConsentType;
use App\Models\Patient;
use App\Models\PatientConsent;
use App\Services\PatientConsentPdfService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class PatientConsentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Patient $patient)
    {
        $patient->load([
            'consents' => function($query){
                $query->orderByDesc('id');
            },
            'consents.consentType',
            'consents.consentVersion',
            'consents.recordedBy',
        ]);

        return inertia('PatientConsents/IndexPatientConsents', [
            'patient' => $patient,
            'patientConsents' => $patient->consents,
            'columns' => [
                'id' => 'ID',
                'consent_type.name' => 'Tipologia consenso',
                'consent_version.version' => 'Versione',
                'acquisition_method' => 'Metodo di acquisizione',
                'recorded_by' => 'Registrato da',
                'pdf_path' => 'Documento',
                'created_at' => 'Inserito il',
                'deleted_at' => 'Cancellato il'
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Patient $patient)
    {
        $consentTypes = ConsentType::query()
            ->where('is_active', true)
            ->whereHas('versions', function($query){
                $query->where('is_active', true);
            })
            ->with([
                'versions' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->latest('version');
                },
            ])
            ->get();

        return inertia('PatientConsents/CreatePatientConsent', [
            'patient' => $patient,
            'consentTypes' => $consentTypes,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientConsentRequest $request, Patient $patient, PatientConsentPdfService $service) {

        $validated = $request->validated();
        foreach($validated['consents'] as $consent){

            $pdfPath = null;
            if ($consent['document'] != null) {
                $pdfPath = $service->store($consent['document']);
            }

            $patient->consents()->create([
                'consent_type_id' => $consent['consent_type_id'],
                'consent_version_id' => $consent['consent_version_id'],
                'status' => $consent['status'],
                'acquisition_method' => $consent['acquisition_method'],
                'recorded_by' => $consent['recorded_by'] ?? auth()->id(),
                'pdf_path' => $pdfPath,
            ]);
        }

        return redirect()
            ->route('admin.patient.consents.index', $patient)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Consenso del paziente registrato correttamente.',
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient, PatientConsent $consent) {

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient, PatientConsent $consent) {
        $consent->load([
            'consentType',
            'consentVersion',
        ]);

        $consentTypes = ConsentType::query()
            ->where('is_active', true)
            ->with([
                'versions' => function ($query) {
                    $query
                        ->where('is_active', true)
                        ->latest('version');
                },
            ])
            ->get();

        return inertia('PatientConsents/EditPatientConsent', [
            'patient' => $patient,
            'patientConsent' => $consent,
            'consentTypes' => $consentTypes,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientConsentRequest $request, Patient $patient, PatientConsent $consent) {
        $formData = $request->validated();

        $pdfPath = null;
        if ($request->hasFile('document')) {
            $pdfPath = $request->file('document')->store(
                'patient-consents',
                'public'
            );
        }

        $patient->consents()->create([
            'consent_type_id' => $formData['consent_type_id'],
            'consent_version_id' => $formData['consent_version_id'],
            'status' => $formData['status'],
            'acquisition_method' => $formData['acquisition_method'],
            'recorded_by' => $formData['recorded_by'] ?? auth()->id(),
            'pdf_path' => $pdfPath,
        ]);

        return redirect()
            ->route('admin.patient.consents.index', $patient)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Consenso del paziente aggiornato correttamente.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient, PatientConsent $consent) {
        $consent->delete();

        return redirect()
            ->route('admin.patient.consents.index', $patient)
            ->with('toast', [
                'type' => 'success',
                'message' => 'Consenso del paziente eliminato correttamente.',
            ]);
    }

    public function document(Patient $patient, PatientConsent $consent){
        abort_unless($consent->patient_id === $patient->id, 404);

        abort_unless($consent->pdf_path, 404);

        $filePath = storage_path('app/' . $consent->pdf_path);

        abort_unless(File::exists($filePath), 404);

        return response()->file($filePath);
    }
}
