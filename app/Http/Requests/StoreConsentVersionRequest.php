<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreConsentVersionRequest extends FormRequest
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
            'content' => [
                'required',
                'string',
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
            'content.required' => 'Il contenuto della versione del consenso è obbligatorio.',
            'content.string' => 'Il contenuto della versione del consenso deve essere un testo valido.',

            'is_active.required' => 'Indicare se la versione del consenso è attiva.',
            'is_active.boolean' => 'Il valore dello stato attivo non è valido.',
        ];
    }
}
