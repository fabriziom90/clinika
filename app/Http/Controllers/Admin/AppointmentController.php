<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Http\Requests\UpdateAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Nationality;
use App\Models\Nurse;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class AppointmentController extends Controller
{
    // public function __construct()
    // {
    //     $this->authorizeResource(\App\Models\Appointment::class, 'appointment');
    // }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->doctor) {
            $appointments = Appointment::with(['doctor.user', 'nurse.user', 'patient', 'service'])
                ->where('doctor_id', $user->doctor->id)
                ->get();
        } elseif ($user->nurse) {
            $appointments = Appointment::with(['doctor.user', 'nurse.user', 'patient', 'service'])
                ->where('nurse_id', $user->nurse->id)
                ->get();
        } else {
            // Admin o altri ruoli autorizzati
            $appointments = Appointment::with(['doctor.user', 'nurse.user', 'patient', 'service'])->get();

        }

        $nationalities = Nationality::all();
        $doctors = Doctor::with(['user', 'services'])->get();
        $nurses = Nurse::with('user')->get();
        $patients = Patient::all();

        return inertia('Calendar/IndexCalendar', [
            'appointments' => $appointments,
            'userIsSuperadmin' => $user->hasRole('superadmin'),
            'doctors' => $doctors,
            'nurses' => $nurses,
            'patients' => $patients,
            'nationalities' => $nationalities,
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
    public function store(StoreAppointmentRequest $request)
    {
        $form_data = $request->validated();

        $startTime = \Carbon\Carbon::parse($form_data['start_time']);
        $duration = $form_data['duration'] ?? 30; // minuti
        $endTime = $startTime->copy()->addMinutes($duration);

        $appointment = new Appointment;
        $appointment->doctor_id = $form_data['doctor_id'];
        $appointment->status = AppointmentStatus::Scheduled;
        $appointment->nurse_id = $form_data['nurse_id'];
        $appointment->patient_id = $form_data['patient_id'];
        $appointment->service_id = $form_data['service_id'];
        $appointment->start_time = $startTime;
        $appointment->end_time = $endTime;
        $appointment->duration_minutes = $duration;
        $appointment->notes = $form_data['notes'] ?? null;

        $appointment->save();

        return redirect()->back()->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Appuntamento creato correttamente.',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Appointment $appointment)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Appointment $appointment)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $form_data = $request->validated();

        $startTime = \Carbon\Carbon::parse($form_data['start_time']);
        $duration = $form_data['duration'] ?? 30; // minuti
        $endTime = $startTime->copy()->addMinutes($duration);

        $appointment->doctor_id = $form_data['doctor_id'];
        $appointment->nurse_id = $form_data['nurse_id'];
        $appointment->patient_id = $form_data['patient_id'];
        $appointment->service_id = $form_data['service_id'];
        $appointment->start_time = $startTime;
        $appointment->end_time = $endTime;
        $appointment->duration_minutes = $duration;
        $appointment->notes = $form_data['notes'] ?? null;

        $appointment->save();

        return redirect()->back()->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Appuntamento modificato correttamente.',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()->route('admin.appointments.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Appuntamento cancellato correttamente',
            ],
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment){
        $validated = $request->validate([
            'status' => ['required', new Enum(AppointmentStatus::class)],
        ]);

        $appointment->update(['status' => $validated['status']]);

        return redirect()->back();
    }
}
