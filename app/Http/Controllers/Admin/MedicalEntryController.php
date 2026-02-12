<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalEntry;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MedicalEntryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $medicalRecord = \App\Models\MedicalRecord::with('medicalEntries.doctor.user', 'medicalEntries.appointment')->findOrFail($medicalRecordId);

        $this->authorize('view', $medicalRecord->medicalEntries()->firstOrFail()); // policy

        return Inertia::render('MedicalEntries/Timeline', [
            'medicalRecord' => $medicalRecord,
            'entries' => $medicalRecord->medicalEntries,
        ]);
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
        $data = $request->validated();
        $data['doctor_id'] = Auth::user()->doctor->id; // medico loggato

        $entry = MedicalEntry::create($data);

        return redirect()->back()->with('success', 'Entry aggiunta con successo');
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
        $data = $request->validated();
        $data['doctor_id'] = Auth::user()->doctor->id;
        $data['previous_entry_id'] = $medicalEntry->id;

        $newEntry = MedicalEntry::create($data);

        return redirect()->back()->with('success', 'Entry aggiornata creando nuova versione');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $this->authorize('delete', $medicalEntry);

        $medicalEntry->delete();

        return redirect()->back()->with('success', 'Entry eliminata');
    }
}
