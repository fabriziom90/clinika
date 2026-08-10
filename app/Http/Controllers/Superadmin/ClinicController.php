<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ClinicController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Clinic::withTrashed();

        if ($request->filled('search')) {
            $search = $request->string('search');

            $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'active') {
            $query->whereNull('deleted_at')
                ->where('active', true);
        }

        if ($request->status === 'inactive') {
            $query->whereNull('deleted_at')
                ->where('active', false);
        }

        if ($request->status === 'deleted') {
            $query->whereNotNull('deleted_at');
        }

        $clinics = $query
            ->orderBy('deleted_at')
            ->orderBy('name')
            ->paginate(20);

        $clinics->appends($request->query());

        return Inertia::render('Superadmin/Clinics/IndexClinics', [
            'clinics' => $clinics,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
            ],
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
    public function store(Request $request, TenantDatabaseService $tenantDatabaseService)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:10'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'logo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'database' => ['required', 'string', 'max:255'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'string', 'max:10'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        $validated['db_password'] = $validated['db_password'] ?? '';

        try {
            $slug = Str::slug($validated['name']);

            $baseSlug = $slug;
            $counter = 2;

            while (Clinic::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }

            if ($request->hasFile('logo_path')) {
                $validated['logo_path'] = $request->file('logo_path')->store('clinics/logos', 'public');
            }

            $validated['slug'] = $slug;

            $clinic = Clinic::create($validated);

            $tenantDatabaseService->createDatabase($clinic);

            $exitCode = Artisan::call('migrate:tenant', [
                'clinic_id' => $clinic->id,
            ]);

            if ($exitCode !== 0) {
                throw new \RuntimeException('Impossibile eseguire le migration del database tenant.');
            }

            return redirect()
                ->route('superadmin.clinics.index')
                ->with('toast', [
                    'type' => 'success',
                    'message' => 'Clinica e database creati correttamente.',
                ]);

        } catch (\Throwable $e) {
            report($e);

            if (isset($clinic)) {
                $clinic->forceDelete();
            }

            return back()
                ->withErrors([
                    'error' => 'Impossibile creare la clinica: '.$e->getMessage(),
                ])
                ->withInput();
        }
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
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:10'],
            'zip_code' => ['nullable', 'string', 'max:10'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'tax_code' => ['nullable', 'string', 'max:50'],
            'logo_path' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'database' => ['required', 'string', 'max:255'],
            'db_host' => ['required', 'string', 'max:255'],
            'db_port' => ['required', 'string', 'max:10'],
            'db_username' => ['required', 'string', 'max:255'],
            'db_password' => ['nullable', 'string', 'max:255'],
            'active' => ['boolean'],
        ]);

        try {
            $slug = Str::slug($validated['name']);
            $baseSlug = $slug;
            $counter = 2;

            while (Clinic::where('slug', $slug)->where('id', '!=', $clinic->id)->exists()) {
                $slug = $baseSlug.'-'.$counter++;
            }

            $validated['slug'] = $slug;

            if ($request->hasFile('logo_path')) {

                if ($clinic->logo_path && Storage::disk('public')->exists($clinic->logo_path)) {
                    Storage::disk('public')->delete($clinic->logo_path);
                }

                $validated['logo_path'] = $request->file('logo_path')->store('clinics/logos', 'public');
            } else {
                unset($validated['logo_path']);
            }

            if (blank($validated['db_password'] ?? null)) {
                unset($validated['db_password']);
            }

            $clinic->update($validated);

            return redirect()->route('superadmin.clinics.index')->with('toast', ['type' => 'success', 'message' => 'Clinica aggiornata correttamente.']);
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['error' => 'Impossibile aggiornare la clinica: '.$e->getMessage()])->withInput();
        }
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
