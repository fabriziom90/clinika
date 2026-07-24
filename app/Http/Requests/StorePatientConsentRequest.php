<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePatientConsentRequest extends FormRequest
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
            'consents' => [
                'required',
                'array',
            ],

            'consents.*.consent_type_id' => [
                'required',
                'integer',
                'exists:consent_types,id',
            ],

            'consents.*.consent_version_id' => [
                'required',
                'integer',
                // verifica che appartenga al consent_type_id della stessa riga
            ],

            'consents.*.status' => [
                'required',
                'string',
                Rule::in([
                    'pending',
                    'accepted',
                    'rejected',
                    'revoked',
                ]),
            ],

            'consents.*.acquisition_method' => [
                'required',
                'string',
                Rule::in([
                    'paper',
                    'upload',
                    'electronic_signature',
                ]),
            ],

            'consents.*.document' => [
                'nullable',
                'file',
                'mimes:pdf,jpg,jpeg,png',
                'max:10240',
            ],
        ];
    }

    /**
     * Get the custom messages for the validator.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'consents.required' => 'È necessario specificare i consensi del paziente.',
            'consents.array' => 'Il formato dei consensi non è valido.',

            'consents.*.consent_type_id.required' => 'La tipologia di consenso è obbligatoria.',
            'consents.*.consent_type_id.integer' => 'La tipologia di consenso selezionata non è valida.',
            'consents.*.consent_type_id.exists' => 'La tipologia di consenso selezionata non esiste.',

            'consents.*.consent_version_id.required' => 'La versione del consenso è obbligatoria.',
            'consents.*.consent_version_id.integer' => 'La versione del consenso selezionata non è valida.',

            'consents.*.status.required' => 'Lo stato del consenso è obbligatorio.',
            'consents.*.status.string' => 'Lo stato del consenso non è valido.',
            'consents.*.status.in' => 'Lo stato del consenso selezionato non è valido.',

            'consents.*.acquisition_method.required' => 'La modalità di acquisizione è obbligatoria.',
            'consents.*.acquisition_method.string' => 'La modalità di acquisizione non è valida.',
            'consents.*.acquisition_method.in' => 'La modalità di acquisizione selezionata non è valida.',

            'consents.*.document.file' => 'Il documento caricato non è valido.',
            'consents.*.document.mimes' => 'Il documento deve essere un file PDF, JPG, JPEG o PNG.',
            'consents.*.document.max' => 'Il documento non può superare i 10 MB.',
        ];
    }
}
