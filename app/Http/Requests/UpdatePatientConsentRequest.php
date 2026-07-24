<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePatientConsentRequest extends FormRequest
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
            'consent_type_id' => [
                'required',
                'integer',
                'exists:consent_types,id',
            ],

            'consent_version_id' => [
                'required',
                'integer',
                Rule::exists('consent_versions', 'id')
                    ->where(function ($query) {
                        $query->where(
                            'consent_type_id',
                            $this->consent_type_id
                        );
                    }),
            ],

            'status' => [
                'required',
                'string',
                Rule::in([
                    'pending',
                    'accepted',
                    'rejected',
                    'revoked',
                ]),
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

            'document' => [
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
            'consent_type_id.required' =>
                'La tipologia di consenso è obbligatoria.',

            'consent_type_id.integer' =>
                'La tipologia di consenso selezionata non è valida.',

            'consent_type_id.exists' =>
                'La tipologia di consenso selezionata non esiste.',

            'consent_version_id.required' =>
                'La versione del consenso è obbligatoria.',

            'consent_version_id.integer' =>
                'La versione del consenso selezionata non è valida.',

            'consent_version_id.exists' =>
                'La versione selezionata non appartiene alla tipologia di consenso indicata.',

            'status.required' =>
                'Lo stato del consenso è obbligatorio.',

            'status.in' =>
                'Lo stato del consenso selezionato non è valido.',

            'acquisition_method.required' =>
                'La modalità di acquisizione è obbligatoria.',

            'acquisition_method.in' =>
                'La modalità di acquisizione selezionata non è valida.',

            'document.file' =>
                'Il documento caricato non è valido.',

            'document.mimes' =>
                'Il documento deve essere un file PDF, JPG, JPEG o PNG.',

            'document.max' =>
                'Il documento non può superare i 10 MB.',
        ];
    }
}
