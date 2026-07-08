<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientHealtHistoryRequest extends FormRequest
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
            'patient_id' => ['required', 'exists:patients,id'],

            'change_reason' => ['nullable', 'string', 'max:1000'],

            'allergies' => ['nullable', 'string', 'max:5000'],
            'chronic_diseases' => ['nullable', 'string', 'max:5000'],
            'current_therapies' => ['nullable', 'string', 'max:5000'],
            'surgical_history' => ['nullable', 'string', 'max:5000'],
            'family_history' => ['nullable', 'string', 'max:5000'],
            'lifestyle' => ['nullable', 'string', 'max:5000'],
            'vaccinations' => ['nullable', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:10000'],
        ];
    }

    public function messages(): array
    {
        return [
            'patient_id.required' => 'Il paziente è obbligatorio.',
            'patient_id.exists' => 'Il paziente selezionato non è valido.',

            'change_reason.string' => 'Il motivo della modifica deve essere un testo.',
            'change_reason.max' => 'Il motivo della modifica non può superare i 1000 caratteri.',

            'allergies.string' => 'Le allergie devono essere un testo.',
            'allergies.max' => 'Le allergie non possono superare i 5000 caratteri.',

            'chronic_diseases.string' => 'Le patologie croniche devono essere un testo.',
            'chronic_diseases.max' => 'Le patologie croniche non possono superare i 5000 caratteri.',

            'current_therapies.string' => 'Le terapie in corso devono essere un testo.',
            'current_therapies.max' => 'Le terapie in corso non possono superare i 5000 caratteri.',

            'surgical_history.string' => 'Lo storico chirurgico deve essere un testo.',
            'surgical_history.max' => 'Lo storico chirurgico non può superare i 5000 caratteri.',

            'family_history.string' => 'L\'anamnesi familiare deve essere un testo.',
            'family_history.max' => 'L\'anamnesi familiare non può superare i 5000 caratteri.',

            'lifestyle.string' => 'Lo stile di vita deve essere un testo.',
            'lifestyle.max' => 'Lo stile di vita non può superare i 5000 caratteri.',

            'vaccinations.string' => 'Le vaccinazioni devono essere un testo.',
            'vaccinations.max' => 'Le vaccinazioni non possono superare i 5000 caratteri.',

            'notes.string' => 'Le note devono essere un testo.',
            'notes.max' => 'Le note non possono superare i 10000 caratteri.',
        ];
    }
}
