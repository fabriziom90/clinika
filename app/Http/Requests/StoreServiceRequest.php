<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreServiceRequest extends FormRequest
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
            'services' => ['required', 'array', 'min:1'],

            'services.*.name' => ['required', 'string', 'max:255'],
            'services.*.default_duration' => ['required', 'integer', 'min:1'],
            'services.*.default_price' => ['required', 'numeric', 'min:0'],
            'services.*.active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'services.requires' => 'Devi inserire almeno una prestazione',
            'services.*.name.required' => 'Il nome della prestazione è obbligatorio.',
            'services.*.name.min' => 'Il nome della prestazione deve contenere almeno 3 caratteri.',
            'services.*.name.unique' => 'Esiste già una prestazione con questo nome.',

            'services.*.default_duration.required' => 'La durata della prestazione è obbligatoria.',
            'services.*.default_duration.integer' => 'La durata deve essere espressa in minuti.',
            'services.*.default_duration.min' => 'La durata minima è di 5 minuti.',
            'services.*.default_duration.max' => 'La durata massima consentita è di 600 minuti.',

            'services.*.default_price.required' => 'Il prezzo della prestazione è obbligatorio.',
            'services.*.default_price.numeric' => 'Il prezzo deve essere un valore numerico.',
            'services.*.default_price.min' => 'Il prezzo non può essere negativo.',
            'services.*.default_price.max' => 'Il prezzo massimo consentito è 9.999,99 €.',

            'services.*.active.required' => 'Devi indicare se la prestazione è attiva.',
            'services.*.active.boolean' => 'Valore non valido per lo stato della prestazione.',
        ];
    }
}
