<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePatientHealtHistoryRequest;
use App\Http\Requests\UpdatePatientHealtHistoryRequest;
use App\Models\PatientHealthHistory;
use App\Models\PatientHealtHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use OwenIt\Auditing\Audit;

class PatientHealthHistoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
    public function store(StorePatientHealtHistoryRequest $request)
    {
        $data = $request->validated();

        $userId = Auth::id();

        return DB::transaction(function () use ($data, $userId) {
            // 1 - deactivate current version
            PatientHealthHistory::where('patient_id', $data['patient_id'])
                ->where('is_current', true)
                ->update([
                    'is_current' => false,
                ]);

            // 2 - create new version
            $history = PatientHealthHistory::create([
                'patient_id' => $data['patient_id'],
                'version' => $this->generateVersion($data['patient_id']),
                'user_id' => $userId,
                'change_reason' => $data['change_reason'] ?? null,

                'allergies' => $data['allergies'] ?? null,
                'chronic_diseases' => $data['chronic_diseases'] ?? null,
                'current_therapies' => $data['current_therapies'] ?? null,
                'surgical_history' => $data['surgical_history'] ?? null,
                'family_history' => $data['family_history'] ?? null,
                'lifestyle' => $data['lifestyle'] ?? null,
                'vaccinations' => $data['vaccinations'] ?? null,
                'notes' => $data['notes'] ?? null,

                'is_current' => true,
            ]);

            Audit::forceCreate([
                'user_id' => auth()->id(),
                'user_type' => get_class(auth()->user()),
                'event' => 'created',
                'auditable_type' => 'App\Models\PatientHealthHistory',
                'auditable_id' => null,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'old_values' => [],
                'new_values' => [],
            ]);

            return redirect()->back()->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Anamnesi aggiornata con successo',
                ],
            ]);
        });
    }

    /**
     * Display the specified resource.
     */
    public function show(PatientHealtHistory $patientHealtHistory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PatientHealtHistory $patientHealtHistory)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePatientHealtHistoryRequest $request, PatientHealtHistory $patientHealtHistory)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PatientHealtHistory $patientHealtHistory)
    {
        //
    }

    private function generateVersion(int $patientId): string
    {
        $last = PatientHealthHistory::where('patient_id', $patientId)->orderByDesc('id')->first();

        if (! $last) {
            return 01;
        }

        return str_pad(((int) $last->version) + 1, 2, '0', STR_PAD_LEFT);
    }
}
