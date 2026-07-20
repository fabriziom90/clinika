<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConsentTypeRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'acquisition_method' => [
                'required',
                'string',
                Rule::in([
                    'paper',
                    'upload',
                    'electronic_signature',
                ]),
            ],

            'is_required' => [
                'required',
                'boolean',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome del consenso è obbligatorio.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',

            'description.string' => 'La descrizione deve essere un testo valido.',

            'acquisition_method.required' => 'La modalità di acquisizione è obbligatoria.',
            'acquisition_method.in' => 'La modalità di acquisizione selezionata non è valida.',

            'is_required.required' => 'Indicare se il consenso è obbligatorio.',
            'is_required.boolean' => 'Il valore del consenso obbligatorio non è valido.',

            'is_active.required' => 'Il valore dello stato attivo è obbligatorio.',
            'is_active.boolean' => 'Il valore dello stato attivo non è valido.',
        ];
    }
}
