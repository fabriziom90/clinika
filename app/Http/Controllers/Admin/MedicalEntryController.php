<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalEntryRequest;
use App\Http\Requests\UpdateMedicalEntryRequest;
use App\Models\Appointment;
use App\Models\MedicalEntry;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class MedicalEntryController extends Controller
{
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
        $entry = MedicalEntry::create($data);
        
       
        // CREAZIONE PARAMETRI VITALI (solo se presenti)
        if (! empty($data['vital_parameters'])) {
            $vitals = $data['vital_parameters'];
            // Filtra solo valori non null
            $hasValues = collect($vitals)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty();

            if ($hasValues) {
                $entry->vitalParameters()->create($vitals);
            }
        }

        // CREAZIONE PRESCRIZIONI (solo se presenti)
        if (! empty($data['prescriptions'])) {
            foreach ($data['prescriptions'] as $prescriptionData) {
                // Assicuriamoci di non creare prescrizioni vuote
                if (! empty($prescriptionData['drug_name'] ?? null)) {
                    $entry->prescriptions()->create($prescriptionData);
                }
            }
        }

        $entry->load([
            'vitalParameters',
            'prescriptions',
            'attachments',
            'doctor.user',
        ]);

        $appointment = $entry->appointment()->with([
            'medicalEntry.vitalParameters',
            'medicalEntry.prescriptions',
            'medicalEntry.attachments',
            'doctor.user',
        ])->first();

        // LOG AUDIT
        Audit::forceCreate([
            'user_id' => $user->id,
            'user_type' => get_class($user),
            'event' => 'create',
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
    public function update(UpdateMedicalEntryRequest $request, string $id)
    {
        // $data = $request->validated();
        // $data['doctor_id'] = Auth::user()->doctor->id;
        // $data['previous_entry_id'] = $medicalEntry->id;

        // $newEntry = MedicalEntry::create($data);

        // return redirect()->back()->with('success', 'Entry aggiornata creando nuova versione');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        // $this->authorize('delete', $medicalEntry);

        // $medicalEntry->delete();

        // return redirect()->back()->with('success', 'Entry eliminata');
    }
}
