<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAdminRequest;
use App\Http\Requests\UpdateAdminRequest;
use App\Mail\PersonSetPasswordMail;
use App\Models\Clinic;
use App\Models\User;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminController extends Controller
{
    public function __construct(
        private TenantDatabaseService $tenantDatabaseService
    ) {}

    /**
     * Display a listing of all tenant admins.
     */
    public function index()
    {
        $clinics = Clinic::on('central')
            ->where('active', true)
            ->orderBy('name')
            ->get();

        $admins = collect();

        foreach ($clinics as $clinic) {
            $this->tenantDatabaseService->connect($clinic);

            $tenantAdmins = User::role('admin')
                ->get()
                ->map(function ($user) use ($clinic) {
                    return [
                        'id' => $user->id,
                        'clinic_id' => $clinic->id,
                        'clinic_name' => $clinic->name,
                        'name' => $user->name,
                        'surname' => $user->surname,
                        'email' => $user->email,
                        'created_at' => $user->created_at?->format('d/m/Y'),
                    ];
                });

            $admins = $admins->merge($tenantAdmins);
        }

        return Inertia::render('Superadmin/Admins/IndexAdmins', [
            'admins' => $admins->values(),
            'columns' => [
                'id' => 'ID',
                'name' => 'Nome',
                'surname' => 'Cognome',
                'email' => 'Email',
                'clinic_name' => 'Clinica',
                'created_at' => 'Inserito il',
            ],
        ]);
    }

    /**
     * Show the form for creating a new admin.
     */
    public function create()
    {
        $clinics = Clinic::on('central')
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return Inertia::render('Superadmin/Admins/CreateAdmin', [
            'clinics' => $clinics,
        ]);
    }

    /**
     * Store a newly created admin inside the selected tenant.
     */
    public function store(StoreAdminRequest $request)
    {
        $formData = $request->validated();

        $clinic = Clinic::on('central')->findOrFail($formData['clinic_id']);

        $this->tenantDatabaseService->connect($clinic);

        if (User::where('email_hash', hash('sha256', mb_strtolower(trim($formData['email']))))->exists()) {
            return back()
                ->withErrors([
                    'email' => 'Esiste già un utente con questo indirizzo email nella clinica selezionata.',
                ])
                ->withInput();
        }

        $password = Str::random(32);

        $user = User::create([
            'name' => $formData['name'],
            'surname' => $formData['surname'],
            'email' => $formData['email'],
            'password' => Hash::make($password),
        ]);

        $user->assignRole('admin');

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

        Mail::to($user->email)->send(
            new PersonSetPasswordMail($user, $clinic, $token)
        );

        return redirect()
            ->route('superadmin.admins.index')
            ->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Admin creato con successo. È stata inviata l’email per impostare la password.',
                ],
            ]);
    }

    /**
     * Display the specified admin.
     */
    public function show(int $clinic, int $admin)
    {
        $clinic = Clinic::on('central')->findOrFail($clinic);

        $this->tenantDatabaseService->connect($clinic);

        $admin = User::role('admin')->findOrFail($admin);

        return Inertia::render('Superadmin/Admins/ShowAdmin', [
            'admin' => [
                'id' => $admin->id,
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'name' => $admin->name,
                'surname' => $admin->surname,
                'email' => $admin->email,
                'created_at' => $admin->created_at?->format('d/m/Y H:i'),
                'updated_at' => $admin->updated_at?->format('d/m/Y H:i'),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified admin.
     */
    public function edit(int $clinic, int $admin)
    {
        $clinic = Clinic::on('central')->findOrFail($clinic);
        $clinics = Clinic::on('central')->get();

        $this->tenantDatabaseService->connect($clinic);

        $admin = User::role('admin')->findOrFail($admin);

        return Inertia::render('Superadmin/Admins/EditAdmin', [
            'admin' => [
                'id' => $admin->id,
                'clinic_id' => $clinic->id,
                'clinic_name' => $clinic->name,
                'name' => $admin->name,
                'surname' => $admin->surname,
                'email' => $admin->email,
            ],
            'clinics' => $clinics,
        ]);
    }

    /**
     * Update the specified admin.
     */
    public function update(UpdateAdminRequest $request, int $clinic, int $admin)
    {
        $formData = $request->validated();

        $clinic = Clinic::on('central')->findOrFail($clinic);

        $this->tenantDatabaseService->connect($clinic);

        $user = User::role('admin')->findOrFail($admin);

        if (
            User::where('email_hash', hash('sha256', mb_strtolower(trim($formData['email']))))
                ->where('id', '!=', $user->id)
                ->exists()
        ) {
            return back()
                ->withErrors([
                    'email' => 'Esiste già un utente con questo indirizzo email nella clinica selezionata.',
                ])
                ->withInput();
        }

        $user->update([
            'name' => $formData['name'],
            'surname' => $formData['surname'],
            'email' => $formData['email'],
        ]);

        return redirect()
            ->route('superadmin.admins.index')
            ->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Admin aggiornato con successo.',
                ],
            ]);
    }

    /**
     * Remove the specified admin.
     */
    public function destroy(int $clinic, int $admin)
    {
        $clinic = Clinic::on('central')->findOrFail($clinic);

        $this->tenantDatabaseService->connect($clinic);

        $user = User::role('admin')->findOrFail($admin);

        $user->delete();

        return redirect()
            ->route('superadmin.admins.index')
            ->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Admin cancellato correttamente.',
                ],
            ]);
    }

    /**
     * Send a new password reset email.
     */
    public function sendResetEmail(int $clinic, int $admin)
    {
        $clinic = Clinic::on('central')->findOrFail($clinic);

        $this->tenantDatabaseService->connect($clinic);

        $user = User::role('admin')->findOrFail($admin);

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

        Mail::to($user->email)->send(
            new PersonSetPasswordMail($user, $clinic, $token)
        );

        return back()->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Email di impostazione password inviata con successo.',
            ],
        ]);
    }
}
