<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBookingRequest;
use App\Models\Appointment;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Services\Connection\TenantDatabaseService;
use App\Services\TenantResolver;
use Carbon\Carbon;
use Inertia\Inertia;

class BookingController extends Controller
{
    public function __construct(
        protected TenantResolver $tenantResolver,
        protected TenantDatabaseService $tenantDatabaseService
    ) {}

    public function index()
    {
        $clinics = Clinic::query()
            ->where('active', true)
            ->orderBy('name')
            ->get([
                'id',
                'uuid',
                'name',
                'city',
                'province',
            ]);

        return Inertia::render('BookingPage', [
            'clinics' => $clinics,
        ]);
    }

    public function doctors(Clinic $clinic)
    {
        if ($clinic->trashed() || ! $clinic->active) {
            abort(404);
        }

        $this->tenantDatabaseService->connect($clinic);

        $doctors = Doctor::query()
            ->with([
                'user',
                'specialty',
            ])
            ->whereHas('user')
            ->orderBy('id')
            ->get()
            ->map(function (Doctor $doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->user->name,
                    'surname' => $doctor->user->surname,
                    'specialty' => $doctor->specialty
                        ? [
                            'id' => $doctor->specialty->id,
                            'name' => $doctor->specialty->name,
                        ]
                        : null,
                ];
            });

        return response()->json($doctors);
    }

    public function services(Clinic $clinic, int $doctor)
    {

        if ($clinic->trashed() || ! $clinic->active) {
            abort(404);
        }

        $this->tenantDatabaseService->connect($clinic);

        $doctor = Doctor::query()
            ->findOrFail($doctor);

        $services = $doctor->services()
            ->wherePivot('active', true)
            ->orderBy('name')
            ->get([
                'services.id',
                'services.name',
            ])
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'name' => $service->name,
                    'duration_minutes' => $service->pivot->duration_minutes,
                    'price' => $service->pivot->price,
                ];
            });

        return response()->json($services);
    }

    public function appointments(Clinic $clinic, int $doctor)
    {
        if ($clinic->trashed() || ! $clinic->active) {
            abort(404);
        }

        $this->tenantDatabaseService->connect($clinic);

        $doctor = Doctor::query()
            ->findOrFail($doctor);

        $appointments = $doctor->appointments()
            ->whereNotIn('status', [
                'cancelled',
                'no_show',
            ])
            ->where('start_time', '>=', now())
            ->orderBy('start_time')
            ->get([
                'id',
                'start_time',
                'duration_minutes',
            ]);

        return response()->json(
            $appointments->map(function ($appointment) {
                $start = Carbon::parse($appointment->start_time);

                $end = $start->copy()->addMinutes($appointment->duration_minutes);

                return [
                    'id' => $appointment->id,
                    'start' => $start->toIso8601String(),
                    'end' => $end->toIso8601String(),
                ];
            })->values()
        );
    }

    public function store(StoreBookingRequest $request)
    {
        $data = $request->validated();

        $clinic = Clinic::query()
            ->whereKey($data['clinic_id'])
            ->where('active', true)
            ->firstOrFail();

        if ($clinic->trashed()) {
            abort(404);
        }

        $this->tenantDatabaseService->connect($clinic);

        $doctor = Doctor::query()
            ->with('user')
            ->findOrFail($data['doctor_id']);

        $service = $doctor->services()
            ->where('services.id', $data['service_id'])
            ->wherePivot('active', true)
            ->first();

        if (! $service) {
            abort(404);
        }

        $duration = (int) $service->pivot->duration_minutes;

        $start = Carbon::createFromFormat(
            'Y-m-d H:i',
            $data['date'].' '.$data['time']
        );

        $end = $start->copy()->addMinutes($duration);

        /*
         * La prenotazione deve essere compresa nell'orario
         * consentito per le prenotazioni online.
         */
        $openingTime = $start->copy()->setTime(7, 0);
        $closingTime = $start->copy()->setTime(22, 0);

        if ($start < $openingTime || $end > $closingTime) {
            return back()->withErrors([
                'time' => 'L\'orario selezionato non è disponibile.',
            ]);
        }

        /*
         * Verifica che il medico non abbia già un appuntamento
         * sovrapposto.
         */
        $hasConflict = Appointment::query()
            ->where('doctor_id', $doctor->id)
            ->whereNotIn('status', [
                'cancelled',
                'no_show',
            ])
            ->where('start_time', '<', $end)
            ->whereRaw(
                'DATE_ADD(start_time, INTERVAL duration MINUTE) > ?',
                [$start]
            )
            ->exists();

        if ($hasConflict) {
            return back()->withErrors([
                'time' => 'L\'orario selezionato non è più disponibile.',
            ]);
        }

        /*
         * Cerchiamo un eventuale paziente già presente.
         *
         * I campi del Patient sono cifrati, quindi la ricerca
         * viene effettuata sui valori decifrati dal model.
         */
        $patient = Patient::query()
            ->get()
            ->first(function (Patient $patient) use ($data) {
                $sameName = mb_strtolower(trim($patient->name ?? ''))
                    === mb_strtolower(trim($data['name']));

                $sameSurname = mb_strtolower(trim($patient->surname ?? ''))
                    === mb_strtolower(trim($data['surname']));

                $samePhone = ! empty($patient->phone)
                    && $patient->phone === $data['phone'];

                $sameEmail = ! empty($patient->email)
                    && mb_strtolower(trim($patient->email))
                        === mb_strtolower(trim($data['email']));

                return $sameName
                    && $sameSurname
                    && ($samePhone || $sameEmail);
            });

        /*
         * Se il paziente non esiste, creiamo una scheda minima.
         */
        if (! $patient) {
            $patient = Patient::create([
                'name' => $data['name'],
                'surname' => $data['surname'],
                'email' => $data['email'],
                'phone' => $data['phone'],
            ]);
        }

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'start_time' => $start,
            'duration_minutes' => $duration,
            'status' => 'scheduled',
            'first_visit' => $data['first_visit'],
        ]);

        return response()->json([
            'message' => 'Prenotazione effettuata con successo.',
            'appointment_id' => $appointment->id,
        ], 201);
    }
}
