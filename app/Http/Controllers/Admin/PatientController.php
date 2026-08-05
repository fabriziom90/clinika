<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientRequest;
use App\Http\Requests\UpdatePatientRequest;
use App\Models\Appointment;
use App\Models\ConsentType;
use App\Models\Doctor;
use App\Models\Nationality;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\ReminderType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

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
        $user = Auth::user();

        if ($user->doctor) {
            $patients = Patient::whereHas('appointments', function ($query) use ($user) {
                $query->where('doctor_id', $user->doctor->id);
            })->get();

        } else {
            $patients = Patient::all();
        }

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'viewed all patients',
            'auditable_type' => 'App\Models\Patient',
            'auditable_id' => null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);

        return Inertia::render('Patients/IndexPatients',
            [
                'patients' => $patients,
                'columns' => [
                    'id' => 'ID',
                    'name' => 'Nome',
                    'surname' => 'Cognome',
                    'email' => 'Email',
                    'phone' => 'Telefono',
                    'created_at' => 'Inserito il',
                ],
            ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $nationalities = Nationality::all();
        $reminderTypes = ReminderType::where('active', true)->get();
        $consentTypes = ConsentType::query()
            ->where('is_active', true)
            ->with([
                'versions' => function ($query) {
                    $query->where('is_active', true)->latest('version');
                },
            ])->get();

        return Inertia::render('Patients/CreatePatient', ['nationalities' => $nationalities, 'reminderTypes' => $reminderTypes, 'consentTypes' => $consentTypes]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePatientRequest $request)
    {
        $form_data = $request->validated();

        $newPatient = new Patient;
        $newPatient->fill($form_data);

        $newPatient->save();

        app(\App\Observers\PatientObserver::class)->created($newPatient);

        $newPatient->reminderTypes()->sync(
            $request->input('reminder_types', []),
        );

        // if request comes from create appointment modal
        if ($request->boolean('inline')) {
            $doctors = Doctor::with('user')->get();
            $nurses = Nurse::with('user')->get();
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
                'appointments' => $appointments,
                'userIsAdmin' => auth()->user()->hasRole('admin'),
            ]);
        }

        if (ConsentType::where('is_active', true)->exists()) {
            return redirect()
                ->route('admin.patient.consents.create', $newPatient)
                ->with([
                    'toast' => [
                        'type' => 'success',
                        'message' => 'Paziente aggiunto correttamente. Ora puoi registrare i consensi.',
                    ],
                ]);
        }

        // Nessun consenso attivo: vai all'elenco pazienti.
        return redirect()
            ->route('admin.patients.index')
            ->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Paziente aggiunto correttamente.',
                ],
            ]);

    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {

        $user = auth()->user();

        $patient = Patient::with([
            'nationality',
            'reminderTypes',
            'patientHistories',
            'patientHistories.author',
            'appointments' => function ($q) use ($user) {
                if (! $user->hasRole('admin')) {
                    $q->where('doctor_id', $user->doctor->id ?? null);
                }

                $q->orderByDesc('start_time')
                    ->with([
                        'medicalEntry.doctor.user',
                        'medicalEntry.appointment',
                        'medicalEntry.latestActiveVersion.attachments',
                        'medicalEntry.latestActiveVersion.prescriptions',
                        'medicalEntry.latestActiveVersion.vitalParameters',
                        'medicalEntry.versions.attachments',
                        'medicalEntry.versions.prescriptions',
                        'medicalEntry.versions.vitalParameters',
                    ]);
            },
            'medicalRecord.medicalEntries' => function ($q) use ($user) {
                if (! $user->hasRole('admin')) {
                    $q->where('doctor_id', $user->doctor->id ?? null);
                }

                $q->orderByDesc('created_at')
                    ->with([
                        'doctor.user',
                        'appointment',
                        'latestActiveVersion.attachments',
                        'latestActiveVersion.prescriptions',
                        'latestActiveVersion.vitalParameters',
                    ]);
            },

        ])->findOrFail($patient->id);

        app(\App\Observers\PatientObserver::class)->viewed($patient);

        return Inertia::render('Patients/ShowPatient', ['patient' => $patient]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $patient->load(['reminderTypes', 'consents' => function ($query) {
            $query->latest();
        }]);

        $nationalities = Nationality::all();
        $reminderTypes = ReminderType::where('active', true)->get();

        return Inertia::render('Patients/EditPatient', ['nationalities' => $nationalities, 'reminderTypes' => $reminderTypes, 'patient' => $patient]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $form_data = $request->validated();
        $patient->update($form_data);

        $patient->reminderTypes()->sync(
            $request->input('reminder_types', [])
        );

        app(\App\Observers\PatientObserver::class)->updated($patient);

        return redirect()->route('admin.patients.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Paziente modificato correttamente.',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        $patient->delete();

        app(\App\Observers\PatientObserver::class)->deleted($patient);

        return redirect()->route('admin.patients.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Paziente cancellato correttamente',
            ],
        ]);
    }

    public function search(Request $request)
    {

        if (! auth()->user()->can('patient.view')) {
            abort(403);
        }

        $search = trim($request->search);

        if ($search == '') {
            return response()->json([]);
        }

        $search = mb_strtolower($search);

        $patients = Patient::query()
            ->get()
            ->filter(function ($patient) use ($search) {

                return str_contains(
                    mb_strtolower($patient->name),
                    $search
                )
                ||
                str_contains(
                    mb_strtolower($patient->surname),
                    $search
                )
                ||
                str_contains(
                    mb_strtolower($patient->personal_code),
                    $search
                );
            })
            ->take(10)
            ->values()
            ->map(function ($patient) {
                return [
                    'id' => $patient->id,
                    'name' => $patient->name,
                    'surname' => $patient->surname,
                    'personal_code' => $patient->personal_code,
                ];
            });

        return response()->json($patients);

    }
}
