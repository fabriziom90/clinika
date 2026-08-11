<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Http\Requests\UpdateDoctorRequest;
use App\Mail\PersonSetPasswordMail;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Nationality;
use App\Models\Nurse;
use App\Models\Patient;
use App\Models\Specialty;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class DoctorController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Doctor::class, 'doctor');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $doctors = Doctor::with('user')->get();

        $doctors = $doctors->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user->name ?? '',
                'surname' => $doctor->user->surname ?? '',
                'email' => $doctor->user->email ?? '',
                'phone' => $doctor->phone,
                'created_at' => $doctor->created_at->format('d/m/Y'),
            ];
        });

        return Inertia::render('Doctors/IndexDoctors', [
            'doctors' => $doctors,
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
        $specialties = Specialty::with(['services'])->get();

        return Inertia::render('Doctors/CreateDoctor', ['nationalities' => $nationalities, 'specialties' => $specialties]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreDoctorRequest $request)
    {
        $form_data = $request->validated();

        $clinicSlug = explode('.', $request->getHost())[0];

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->firstOrFail();

        $servicesSync = [];
        $password = Str::random(12);

        $newUser = User::create([
            'name' => $form_data['name'],
            'surname' => $form_data['surname'],
            'email' => $form_data['email'],
            'password' => Hash::make($password),
        ]);

        $newUser->assignRole('doctor');

        $newUser->refresh();

        $doctor = Doctor::create([
            'user_id' => $newUser->id,
            'personal_code' => $form_data['personal_code'],
            'vat' => $form_data['vat'],
            'birthday' => $form_data['birthday'],
            'birth_city' => $form_data['birth_city'],
            'city' => $form_data['city'],
            'address' => $form_data['address'],
            'phone' => $form_data['phone'],
            'genre' => $form_data['genre'],
            'pec' => $form_data['pec'] ?? null,
            'specialty_id' => $form_data['specialty_id'],
            'nationality_id' => $form_data['nationality_id'],
        ]);

        foreach ($form_data['services'] as $service) {
            if (! empty($service['service_id'])) {
                $servicesSync[$service['service_id']] = [
                    'price' => $service['price'],
                    'duration_minutes' => $service['duration'],
                    'active' => $service['active'] ?? 1,
                ];
            }
        }

        $doctor->services()->sync($servicesSync);

        $token = Str::random(64);

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->where('user_id', $newUser->id)
            ->delete();

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->insert([
                'user_id' => $newUser->id,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

        Mail::to($newUser->email)->send(new PersonSetPasswordMail($newUser, $clinic, $token));

        app(\App\Observers\DoctorObserver::class)->created($doctor);

        return redirect()->route('admin.doctors.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Dottore creato con successo.',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Doctor $doctor)
    {

        $user = Auth::user();

        $doctor = Doctor::with(['user', 'nationality', 'specialty', 'appointments.service', 'appointments.patient', 'appointments.doctor', 'appointments.doctor.user', 'services'])->findOrFail($doctor->id);
        $doctors = Doctor::all();
        $patients = Patient::all();
        $nurses = Nurse::all();
        $nationalities = Nationality::all();
        $user = Auth::user();

        app(\App\Observers\DoctorObserver::class)->viewed($doctor);

        return Inertia::render('Doctors/ShowDoctor', ['doctor' => $doctor, 'doctors' => $doctors, 'patients' => $patients, 'nurses' => $nurses, 'nationalities' => $nationalities, 'userIsAdmin' => $user->hasRole('Admin')]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Doctor $doctor)
    {
        $doctor->load(['user', 'services' => function ($service) {
            $service->select('services.id', 'name')->withPivot('price', 'duration_minutes', 'price');
        }]);

        $doctor->services = $doctor->services->map(function ($service) {
            return [
                'service_id' => $service->id,
                'name' => $service->name,
                'price' => $service->pivot->price,
                'duration_minutes' => $service->pivot->duration_minutes,
                'active' => $service->pivot->active,
            ];
        });

        $nationalities = Nationality::all();
        $specialties = Specialty::with(['services'])->get();

        return Inertia::render('Doctors/EditDoctor', ['doctor' => $doctor, 'nationalities' => $nationalities, 'specialties' => $specialties]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $form_data = $request->validated();

        $servicesSync = [];

        $doctor->user->update([
            'name' => $form_data['name'],
            'surname' => $form_data['surname'],
            'email' => $form_data['email'],
        ]);

        $doctor->update([
            'user_id' => $doctor->user->id,
            'personal_code' => $form_data['personal_code'],
            'vat' => $form_data['vat'],
            'birthday' => $form_data['birthday'],
            'birth_city' => $form_data['birth_city'],
            'city' => $form_data['city'],
            'address' => $form_data['address'],
            'phone' => $form_data['phone'],
            'genre' => $form_data['genre'],
            'pec' => $form_data['pec'] ?? null,
            'specialty_id' => $form_data['specialty_id'],
            'nationality_id' => $form_data['nationality_id'],
        ]);

        foreach ($form_data['services'] as $service) {
            if (! empty($service['service_id'])) {
                $servicesSync[$service['service_id']] = [
                    'price' => $service['price'],
                    'duration_minutes' => $service['duration'],
                    'active' => $service['active'] ?? 1,
                ];
            }
        }

        $doctor->services()->sync($servicesSync);

        app(\App\Observers\DoctorObserver::class)->updated($doctor);

        return redirect()->route('admin.doctors.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Dottore aggiornato con successo',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Doctor $doctor)
    {
        $doctor->user()->delete();

        $doctor->delete();

        app(\App\Observers\DoctorObserver::class)->deleted($doctor);

        return redirect()->route('admin.doctors.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Dottore cancellato correttamente',
            ],
        ]);
    }

    public function sendResetEmail(Request $request, int $id)
    {
        $doctor = Doctor::findOrFail($id);
        $user = $doctor->user;

        $clinicSlug = explode('.', $request->getHost())[0];

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->firstOrFail();

        $token = Str::random(64);

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->where('user_id', $user->id)
            ->delete();

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->insert([
                'user_id' => $user->id,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

        Mail::to($user->email)->send(new PersonSetPasswordMail($user, $clinic, $token));

        app(\App\Observers\DoctorObserver::class)->sendResetEmail($doctor);

        return back()->with('toast', [
            'type' => 'success',
            'message' => 'Email di impostazione password inviata con successo',
        ]);
    }
}
