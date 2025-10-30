<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Nationality;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Inertia\Inertia;
use Inertia\Response;

class PatientController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {   
        $patients = Patient::all();
        return Inertia::render('Patients/IndexPatients', 
            [
                'patients' => $patients, 
                'columns' => [
                    'id'    => 'ID',
                    'name'  => 'Nome',
                    'surname'   => 'Cognome',
                    'email'     => 'Email',
                    'phone'     => 'Telefono',
                    'created_at'    => 'Inserito il'
                ] 
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {   
        $nationalities = Nationality::all();
        return Inertia::render('Patients/CreatePatient', ['nationalities' => $nationalities]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request)
    {
        $form_data = $request->validated();

        $newPatient = new Patient();
        $newPatient->fill($form_data);

        $newPatient->save();

        return redirect()->route('admin.patients.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Paziente aggiunto correttamente.',
            ]]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        return Inertia::render('Patients/ShowPatient', ['patient' => $patient]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $nationalities = Nationality::all();
        return Inertia::render('Patients/EditPatient', ['nationalities' => $nationalities, 'patient' => $patient]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $form_data = $request->validated();
        $patient->update($form_data);

        return redirect()->route('admin.patients.index')->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Paziente modificato correttamente.',
                ]
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()->route('admin.patients.index')->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Paziente cancellato correttamente'
                ]
            ]);
    }
}
