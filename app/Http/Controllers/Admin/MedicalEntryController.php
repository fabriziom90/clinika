<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalEntryRequest;
use App\Http\Requests\UpdateMedicalEntryRequest;
use App\Models\MedicalEntry;
use App\Models\MedicalEntryVersion;
use App\Services\MedicalEntryVersionPdfService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use OwenIt\Auditing\Models\Audit;

class MedicalEntryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\MedicalEntry::class, 'medical_entry');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // $medicalRecord = \App\Models\MedicalRecord::with('medicalEntries.doctor.user', 'medicalEntries.appointment')->findOrFail($medicalRecordId);

        // $this->authorize('view', $medicalRecord->medicalEntries()->firstOrFail()); // policy

        // return Inertia::render('MedicalEntries/Timeline', [
        //     'medicalRecord' => $medicalRecord,
        //     'entries' => $medicalRecord->medicalEntries,
        // ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalEntryRequest $request)
    {

        $patient_id = $request->all()['patient_id'];
        $data = $request->validated();
        $user = Auth::user();

        // Associa medico loggato
        $data['doctor_id'] = $user->doctor->id;

        // Creazione entry principale
        $entry = MedicalEntry::create([
            'medical_record_id' => $data['medical_record_id'],
            'appointment_id' => $data['appointment_id'],
            'doctor_id' => $data['doctor_id'],
            'cancelled_by' => null,
            'cancelled_at' => null,
        ]);

        // Creazione prima versione della visita
        $version = $entry->versions()->create([
            'uuid' => Str::uuid(),
            'version' => 1,
            'doctor_id' => $data['doctor_id'],
            'type' => $data['type'] ?? 'visit',
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
        ]);

        // CREAZIONE PARAMETRI VITALI collegati alla versione
        if (! empty($data['vital_parameters'])) {
            $vitals = collect($data['vital_parameters'])->filter(fn ($v) => $v !== null && $v !== '')->toArray();
            if (! empty($vitals)) {
                $version->vitalParameters()->create($vitals);
            }
        }

        // CREAZIONE PRESCRIZIONI collegate alla versione
        if (! empty($data['prescriptions'])) {
            foreach ($data['prescriptions'] as $prescriptionData) {
                if (! empty($prescriptionData['drug_name'] ?? null)) {
                    $version->prescriptions()->create($prescriptionData);
                }
            }
        }

        // Caricamento relazioni per frontend
        $version->load([
            'vitalParameters',
            'prescriptions',
            'attachments',
        ]);

        $appointment = $entry->appointment()->with([
            'medicalEntry.latestActiveVersion.vitalParameters',
            'medicalEntry.latestActiveVersion.prescriptions',
            'medicalEntry.latestActiveVersion.attachments',
            'doctor.user',
        ])->first();

        // LOG AUDIT
        Audit::forceCreate([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'event' => 'created',
            'auditable_type' => get_class($entry),
            'auditable_id' => $entry->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => $entry->toArray(),
        ]);

        return redirect()->route('admin.patients.show', $patient_id)->with('toast', [
            'type' => 'success',
            'message' => 'Visita aggiunta con successo',
        ])->with([
            'appointmentEntry' => $appointment,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalEntryRequest $request, MedicalEntry $medicalEntry)
    {

        $entry = $medicalEntry;
        $data = $request->validated();
        $user = Auth::user();

        // Recupero ultima versione
        $latestVersion = $entry->latestVersion;

        // Se è richiesto il void della versione
        if (! empty($data['is_voided'])) {
            $latestVersion->update([
                'is_voided' => true,
                'voided_at' => now(),
                'voided_by' => $user->doctor->user->id,
                'void_reason' => $data['void_reason'] ?? 'Voided per correzione',
            ]);

            Audit::forceCreate([
                'user_id' => $user->id,
                'user_type' => get_class($user),
                'event' => 'void',
                'auditable_type' => get_class($entry),
                'auditable_id' => $entry->id,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => $latestVersion->toArray(),
                'new_values' => $latestVersion->toArray(),
            ]);

            return redirect()->back()->with('toast', [
                'type' => 'success',
                'message' => 'Versione contrassegnata come annullata',
            ])->with([
                'appointmentEntry' => $entry->appointment()->with([
                    'medicalEntry.latestActiveVersion.vitalParameters',
                    'medicalEntry.latestActiveVersion.prescriptions',
                    'medicalEntry.latestActiveVersion.attachments',
                    'medicalEntry.versions.attachments',
                    'medicalEntry.versions.prescriptions',
                    'medicalEntry.versions.vitalParameters',
                    'doctor.user',
                ])->first(),
            ]);
        }

        // CREAZIONE NUOVA VERSIONE
        $newVersion = $entry->versions()->create([
            'uuid' => Str::uuid(),
            'version' => ($latestVersion->version ?? 0) + 1,
            'type' => $data['type'] ?? $latestVersion->type,
            'title' => $data['title'] ?? $latestVersion->title,
            'content' => $data['content'] ?? $latestVersion->content,
            'change_reason' => $data['change_reason'] ?? 'Modifica visita',
        ]);

        // Aggiorno parametri vitali
        if (! empty($data['vital_parameters'])) {
            $vitals = collect($data['vital_parameters'])->filter(fn ($v) => $v !== null && $v !== '')->toArray();
            if (! empty($vitals)) {
                $newVersion->vitalParameters()->create($vitals);
            }
        }

        // Aggiorno prescrizioni
        if (! empty($data['prescriptions'])) {
            foreach ($data['prescriptions'] as $prescriptionData) {
                if (! empty($prescriptionData['drug_name'] ?? null)) {
                    $newVersion->prescriptions()->create($prescriptionData);
                }
            }
        }

        // Caricamento relazioni per frontend
        $newVersion->load([
            'vitalParameters',
            'prescriptions',
            'attachments',
        ]);

        // LOG AUDIT
        Audit::forceCreate([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'event' => 'updated',
            'auditable_type' => get_class($entry),
            'auditable_id' => $entry->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $latestVersion->toArray(),
            'new_values' => $newVersion->toArray(),
        ]);

        return redirect()->back()->with('toast', [
            'type' => 'success',
            'message' => 'Visita aggiornata creando nuova versione',
        ])->with([
            'appointmentEntry' => $entry->appointment()->with([
                'medicalEntry.latestActiveVersion.vitalParameters',
                'medicalEntry.latestActiveVersion.prescriptions',
                'medicalEntry.latestActiveVersion.attachments',
                'medicalEntry.versions.attachments',
                'medicalEntry.versions.prescriptions',
                'medicalEntry.versions.vitalParameters',
                'doctor.user',
            ])->first(),
        ]);
    }

    public function versionPdf(MedicalEntryVersion $version, MedicalEntryVersionPdfService $pdfService)
    {
        $this->authorize(
            'view',
            $version->medicalEntry->medicalRecord
        );
        $user = Auth::user();
        Audit::forceCreate([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'event' => 'show_pdf',
            'auditable_type' => get_class($version),
            'auditable_id' => $version->id,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => $version->toArray(),
            'new_values' => [],
        ]);

        $path = $pdfService->getPdfPath($version);

        return response()->file(
            storage_path('app/'.$path)
        );
    }
}
