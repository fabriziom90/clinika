<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreNurseRequest;
use App\Http\Requests\UpdateNurseRequest;
use App\Mail\PersonSetPasswordMail;
use App\Models\Clinic;
use App\Models\Nationality;
use App\Models\Nurse;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class NurseController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(\App\Models\Nurse::class, 'nurse');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $nurses = Nurse::with('user')->get();

        $nurses = $nurses->map(function ($nurse) {
            return [
                'id' => $nurse->id,
                'name' => $nurse->user->name ?? '',
                'surname' => $nurse->user->surname ?? '',
                'email' => $nurse->user->email ?? '',
                'phone' => $nurse->phone,
                'created_at' => $nurse->created_at->format('d/m/Y'),
            ];
        });

        return Inertia::render('Nurses/IndexNurses', [
            'nurses' => $nurses,
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

        return Inertia::render('Nurses/CreateNurse', ['nationalities' => $nationalities]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreNurseRequest $request)
    {
        $form_data = $request->validated();

        $clinicSlug = $request->getHost();
        $clinicSlug = explode('.', $clinicSlug)[0];

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->firstOrFail();

        $password = Str::random(12);
        $user = [
            'name' => $form_data['name'],
            'surname' => $form_data['surname'],
            'email' => $form_data['email'],
            'password' => Hash::make($password),
        ];

        $newUser = User::create($user);
        $newUser->assignRole('nurse');

        $nurse = Nurse::create([
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
            'nationality_id' => $form_data['nationality_id'],
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

        return redirect()->route('admin.nurses.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Infermiere creato con successo.',
            ],
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Nurse $nurse)
    {
        $nurse = Nurse::with(['user', 'nationality'])->findOrFail($nurse->id);

        return Inertia::render('Nurses/ShowNurse', ['nurse' => $nurse]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Nurse $nurse)
    {
        $nurse->load('user');
        $nationalities = Nationality::all();

        return Inertia::render('Nurses/EditNurse', ['nurse' => $nurse, 'nationalities' => $nationalities]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateNurseRequest $request, Nurse $nurse)
    {
        $form_data = $request->validated();

        $nurse->user->update([
            'name' => $form_data['name'],
            'surname' => $form_data['surname'],
            'email' => $form_data['email'],
        ]);

        $nurse->update([
            'user_id' => $nurse->user->id,
            'personal_code' => $form_data['personal_code'],
            'vat' => $form_data['vat'],
            'birthday' => $form_data['birthday'],
            'birth_city' => $form_data['birth_city'],
            'city' => $form_data['city'],
            'address' => $form_data['address'],
            'phone' => $form_data['phone'],
            'genre' => $form_data['genre'],
            'pec' => $form_data['pec'] ?? null,
            'nationality_id' => $form_data['nationality_id'],
        ]);

        return redirect()->route('admin.nurses.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Infermiere aggiornato con successo',
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Nurse $nurse)
    {
        $nurse->user()->delete();

        $nurse->delete();

        return redirect()->route('admin.nurses.index')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Infermiere cancellato correttamente',
            ],
        ]);
    }

    public function sendResetEmail(Request $request, int $id)
    {
        $nurse = Nurse::findOrFail($id);
        $user = $nurse->user;

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

        return back()->with(['toast', [
            'type' => 'success',
            'message' => 'Email di impostazione password inviata con successo',
        ]]);
    }
}
