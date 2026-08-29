<?php

namespace App\Http\Controllers;

use App\Models\Clinic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function edit(Request $request): Response
    {
        $user = $request->user();

        $clinic = null;

        if ($user->hasRole('admin')) {
            $clinicSlug = explode('.', $request->getHost())[0];

            $clinic = Clinic::on('central')
                ->where('slug', $clinicSlug)
                ->firstOrFail();
        }

        return Inertia::render('Profile/Edit', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'surname' => $user->surname,
                'email' => $user->email,
            ],
            'isAdmin' => $user->hasRole('admin'),
            'clinic' => $clinic ? [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'email' => $clinic->email,
                'phone' => $clinic->phone,
                'address' => $clinic->address,
                'city' => $clinic->city,
                'province' => $clinic->province,
                'zip_code' => $clinic->zip_code,
                'vat_number' => $clinic->vat_number,
                'tax_code' => $clinic->tax_code,
                'logo_path' => $clinic->logo_path,
            ] : null,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $userData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
        ]);

        $user->update($userData);

        if (! $user->hasRole('admin')) {
            return Redirect::route('profile.edit')->with([
                'toast' => [
                    'type' => 'success',
                    'message' => 'Profilo aggiornato con successo.',
                ],
            ]);
        }

        $clinicData = $request->validate([
            'clinic_name' => ['required', 'string', 'max:255'],
            'clinic_email' => ['nullable', 'email', 'max:255'],
            'clinic_phone' => ['nullable', 'string', 'max:255'],
            'clinic_address' => ['nullable', 'string', 'max:255'],
            'clinic_city' => ['nullable', 'string', 'max:255'],
            'clinic_province' => ['nullable', 'string', 'max:255'],
            'clinic_zip_code' => ['nullable', 'string', 'max:20'],
            'clinic_vat_number' => ['nullable', 'string', 'max:255'],
            'clinic_tax_code' => ['nullable', 'string', 'max:255'],
            'clinic_logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $clinicSlug = explode('.', $request->getHost())[0];

        $clinic = Clinic::on('central')
            ->where('slug', $clinicSlug)
            ->firstOrFail();

        $clinic->update([
            'name' => $clinicData['clinic_name'],
            'email' => $clinicData['clinic_email'],
            'phone' => $clinicData['clinic_phone'],
            'address' => $clinicData['clinic_address'],
            'city' => $clinicData['clinic_city'],
            'province' => $clinicData['clinic_province'],
            'zip_code' => $clinicData['clinic_zip_code'],
            'vat_number' => $clinicData['clinic_vat_number'],
            'tax_code' => $clinicData['clinic_tax_code'],
        ]);

        if ($request->hasFile('clinic_logo')) {
            if ($clinic->logo_path) {
                Storage::disk('public')->delete($clinic->logo_path);
            }

            $clinic->update([
                'logo_path' => $request->file('clinic_logo')->store('clinics/logos', 'public'),
            ]);
        }

        return Redirect::route('profile.edit')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Profilo e dati della clinica aggiornati con successo.',
            ],
        ]);
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $request->user()->update([
            'password' => Hash::make($request->password),
        ]);

        return Redirect::route('profile.edit')->with([
            'toast' => [
                'type' => 'success',
                'message' => 'Password modificata con successo.',
            ],
        ]);
    }
}
