<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateReminderTypeRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'sent_before_value' => ['required', 'integer', 'min:1'],
            'sent_before_unit' => ['required', 'in:hours,days'],
            'active' => ['required', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Il nome della tipologia è obbligatorio.',
            'name.string' => 'Il nome deve essere una stringa valida.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',

            'description.string' => 'La descrizione deve essere una stringa valida.',

            'sent_before_value.required' => 'La tempistica è obbligatoria.',
            'sent_before_value.integer' => 'La tempistica deve essere un numero intero.',
            'sent_before_value.min' => 'La tempistica deve essere almeno pari a 1.',

            'sent_before_unit.required' => 'Seleziona l\'unità di misura della tempistica.',
            'sent_before_unit.in' => 'L\'unità di misura selezionata non è valida.',

            'active.required' => 'Specifica se la tipologia è attiva.',
            'active.boolean' => 'Il valore del campo attivo non è valido.',
        ];
    }
}
