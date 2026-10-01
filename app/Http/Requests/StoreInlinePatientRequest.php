<?php

namespace App\Http\Requests;

use App\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInlinePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'surname' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique(Patient::class, 'email'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome è obbligatorio.',
            'name.string' => 'Il nome non è valido.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',

            'surname.required' => 'Il cognome è obbligatorio.',
            'surname.string' => 'Il cognome non è valido.',
            'surname.max' => 'Il cognome non può superare i 255 caratteri.',

            'phone.required' => 'Il numero di telefono è obbligatorio.',
            'phone.string' => 'Il numero di telefono non è valido.',
            'phone.max' => 'Il numero di telefono non può superare i 30 caratteri.',

            'email.required' => 'L\'indirizzo email è obbligatorio.',
            'email.email' => 'L\'indirizzo email non è valido.',
            'email.max' => 'L\'indirizzo email non può superare i 255 caratteri.',
            'email.unique' => 'Esiste già un paziente con questo indirizzo email.',
        ];
    }
}
