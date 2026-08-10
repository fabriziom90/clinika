<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAdminRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'clinic_id' => ['required', 'integer', 'exists:central.clinics,id'],
            'name' => ['required', 'string', 'max:255'],
            'surname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome è obbligatorio.',
            'name.string' => 'Il nome deve essere una stringa.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',

            'surname.required' => 'Il cognome è obbligatorio.',
            'surname.string' => 'Il cognome deve essere una stringa.',
            'surname.max' => 'Il cognome non può superare i 255 caratteri.',

            'email.required' => 'L\'indirizzo email è obbligatorio.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.max' => 'L\'indirizzo email non può superare i 255 caratteri.',
            'email.unique' => 'Esiste già un utente con questo indirizzo email.',

            'username.required' => 'Lo username è obbligatorio.',
            'username.string' => 'Lo username deve essere una stringa.',
            'username.max' => 'Lo username non può superare i 255 caratteri.',
            'username.unique' => 'Questo username è già utilizzato.',
        ];
    }
}
