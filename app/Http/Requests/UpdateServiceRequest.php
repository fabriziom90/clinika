<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
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
                'min:3',
                'max:255',
                Rule::unique('services', 'name')->ignore($this->service),
            ],
            'default_duration' => 'required|integer|min:5|max:600',
            'default_price' => 'required|numeric|min:0|max:9999.99',
            'active' => 'required|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome della prestazione è obbligatorio.',
            'name.min' => 'Il nome della prestazione deve contenere almeno 3 caratteri.',
            'name.unique' => 'Esiste già una prestazione con questo nome.',

            'default_duration.required' => 'La durata della prestazione è obbligatoria.',
            'default_duration.integer' => 'La durata deve essere espressa in minuti.',
            'default_duration.min' => 'La durata minima è di 5 minuti.',
            'default_duration.max' => 'La durata massima consentita è di 600 minuti.',

            'default_price.required' => 'Il prezzo della prestazione è obbligatorio.',
            'default_price.numeric' => 'Il prezzo deve essere un valore numerico.',
            'default_price.min' => 'Il prezzo non può essere negativo.',
            'default_price.max' => 'Il prezzo massimo consentito è 9.999,99 €.',

            'active.required' => 'Devi indicare se la prestazione è attiva.',
            'active.boolean' => 'Valore non valido per lo stato della prestazione.',
        ];
    }
}
