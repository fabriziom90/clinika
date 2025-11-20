<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Nurse;
use App\Models\Nationality;
use App\Models\Appointment;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Auth;

class PatientController extends Controller
{   
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Patient::class, 'patient');
    }

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

        if ($request->boolean('inline')) {
            $doctors = Doctor::with('user')->get();
            $nurses = Nurse::with('nurse')->get();
            $nationalities = Nationality::all();
            
            $user = Auth::user();

            if ($user->doctor) {
            $appointments = Appointment::with(['doctor.user', 'nurse.user', 'patient'])
                ->where('doctor_id', $user->doctor->id)
                ->get();
            } elseif ($user->nurse) {
                $appointments = Appointment::with(['doctor.user', 'nurse.user', 'patient'])
                    ->where('nurse_id', $user->nurse->id)
                    ->get();
            } else {
                // Admin o altri ruoli autorizzati
                $appointments = Appointment::with(['doctor.user', 'nurse.user', 'patient'])->get();
            }

            return Inertia::render('Calendar/IndexCalendar', [
                'newPerson' => $newPatient,
                'doctors' => $doctors,
                'patients' => Patient::all(),
                'nurses' => $nurses,
                'nationalities' => $nationalities,
                'appointments'  => $appointments,
                'userIsSuperadmin' => auth()->user()->hasRole('superadmin'),
            ]);
        }
        else{

            return redirect()->route('admin.patients.index')->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Paziente aggiunto correttamente.',
                ]]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {   
        $patient = Patient::with('nationality')->findOrFail($patient->id);
        
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
