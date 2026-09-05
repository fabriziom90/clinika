<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSecretaryRequest;
use App\Http\Requests\UpdateSecretaryRequest;
use App\Mail\PersonSetPasswordMail;
use App\Models\Clinic;
use App\Models\Nationality;
use App\Models\Secretary;
use App\Models\User;
use App\Services\EmployeeCodeGeneratorService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class SecretaryController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Secretary::class, 'secretary');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $secretaries = Secretary::with('user')->get();

        $secretaries = $secretaries->map(function ($secretary) {
            return [
                'id' => $secretary->id,
                'name' => $secretary->user->name,
                'surname' => $secretary->user->surname,
                'email' => $secretary->user->email,
                'phone' => $secretary->phone,
                'created_at' => $secretary->created_at->format('d/m/Y'),
            ];
        });

        return Inertia::render('Secretaries/IndexSecretary', [
            'secretaries' => $secretaries,
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
        return Inertia::render('Secretaries/CreateSecretary', [
            'nationalities' => Nationality::all(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSecretaryRequest $request, EmployeeCodeGeneratorService $codeGenerator)
    {
        $form_data = $request->validated();

        $clinicSlug = $request->getHost();
        $clinicSlug = explode('.', $clinicSlug)[0];

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->firstOrFail();

        $password = Str::random(12);

        $newUser = User::create([
            'name' => $form_data['name'],
            'surname' => $form_data['surname'],
            'email' => $form_data['email'],
            'password' => Hash::make($password),
        ]);
        $newUser->assignRole('secretary');

        $employeeCode = $codeGenerator->generate(Secretary::class);
        $secretary = Secretary::create([
            'user_id' => $newUser->id,
            'personal_code' => $form_data['personal_code'],
            'birthday' => $form_data['birthday'],
            'birth_city' => $form_data['birth_city'],
            'city' => $form_data['city'],
            'address' => $form_data['address'],
            'phone' => $form_data['phone'],
            'genre' => $form_data['genre'],
            'nationality_id' => $form_data['nationality_id'],
            'employee_code' => $employeeCode,
            'notes' => $form_data['notes'],
            'zip_code' => $form_data['zip_code'],
        ]);

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

        app(\App\Observers\SecretaryObserver::class)->created($secretary);

        return redirect()->route('admin.secretaries.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Segretaria creata con successo.',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Secretary $secretary)
    {
        $secretary->load('user', 'nationality');

        app(\App\Observers\SecretaryObserver::class)->viewed($secretary);

        return Inertia::render('Secretaries/ShowSecretary', [
            'secretary' => $secretary,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Secretary $secretary)
    {
        $secretary->load('user');
        $nationalities = Nationality::all();

        return Inertia::render('Secretaries/EditSecretary', [
            'secretary' => $secretary,
            'nationalities' => $nationalities,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSecretaryRequest $request, Secretary $secretary)
    {
        $form_data = $request->validated();

        $secretary->user->update([
            'name' => $form_data['name'],
            'surname' => $form_data['surname'],
            'email' => $form_data['email'],
        ]);

        $secretary->update([
            'personal_code' => $form_data['personal_code'],
            'birthday' => $form_data['birthday'],
            'birth_city' => $form_data['birth_city'],
            'city' => $form_data['city'],
            'address' => $form_data['address'],
            'phone' => $form_data['phone'],
            'genre' => $form_data['genre'],
            'nationality_id' => $form_data['nationality_id'],
            'notes' => $form_data['notes'],
            'zip_code' => $form_data['zip_code'],
        ]);

        app(\App\Observers\SecretaryObserver::class)->updated($secretary);

        return redirect()->route('admin.secretaries.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Segretaria modificata con successo.',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Secretary $secretary)
    {
        $secretary->user()->delete();

        $secretary->delete();

        app(\App\Observers\SecretaryObserver::class)->forceDeleted($secretary);

        return redirect()->route('admin.secretaries.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Segretaria cancellata correttamente',
            ],
        ]);
    }

    public function sendResetEmail(Request $request, int $id)
    {
        $secretary = Secretary::findOrFail($id);

        $this->authorize('update', $secretary);

        $user = $secretary->user;

        $clinicSlug = explode('.', $request->getHost())[0];

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->firstOrFail();

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->where('user_id', $user->id)
            ->delete();

        $token = Str::random(64);

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->insert([
                'user_id' => $user->id,
                'token' => Hash::make($token),
                'created_at' => now(),
            ]);

        Mail::to($user->email)->send(new PersonSetPasswordMail($user, $clinic, $token));

        app(\App\Observers\SecretaryObserver::class)->sendResetEmail($secretary);

        return back()->with(['toast', [
            'type' => 'success',
            'message' => 'Email di impostazione password inviata con successo',
        ]]);
    }
}
