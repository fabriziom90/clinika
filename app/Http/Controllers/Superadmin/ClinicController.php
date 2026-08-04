<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clinics = Clinic::withTrashed()
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate(20);

        $clinics->appends(request()->query());

        return Inertia::render('Superadmin/Clinics/IndexClinics', [
            'clinics' => $clinics,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Superadmin/Clinics/CreateClinic');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', 'unique:clinics,slug'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:10'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'logo' => ['nullable', 'string', 'max:255'],
            'database' => ['required', 'string', 'max:255'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'string', 'max:10'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        Clinic::create($validated);

        return redirect()
            ->route('superadmin.clinics.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Clinica creata correttamente.',
            ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Clinic $clinic)
    {
        return Inertia::render('Superadmin/Clinics/ShowClinic', [
            'clinic' => $clinic,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Clinic $clinic)
    {
        return Inertia::render('Superadmin/Clinics/EditClinic', [
            'clinic' => $clinic,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clinic $clinic)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'alpha_dash',
                Rule::unique('central.clinics', 'slug')->ignore($clinic->id),
            ],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:10'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'logo_path' => ['nullable', 'string', 'max:255'],
            'database' => ['required', 'string', 'max:255'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'string', 'max:10'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        if (blank($validated['db_password'] ?? null)) {
            unset($validated['db_password']);
        }

        $clinic->update($validated);

        return redirect()
            ->route('superadmin.clinics.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Clinica aggiornata correttamente.',
            ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clinic $clinic)
    {
        $clinic->delete();

        return redirect()
            ->route('superadmin.clinics.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Clinica disattivata correttamente.',
            ]);
    }

    public function restore(int $clinic)
    {
        $clinic = Clinic::withTrashed()->findOrFail($clinic);

        $clinic->restore();

        return redirect()
            ->route('superadmin.clinics.index')
            ->with('toast', [
                'type' => 'success',
                'message' => 'Clinica riattivata correttamente.',
            ]);
    }
}
