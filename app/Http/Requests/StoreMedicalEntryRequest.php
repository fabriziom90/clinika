<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalEntryRequest extends FormRequest
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
            'medical_record_id' => ['required', 'exists:medical_records,id'],
            'appointment_id' => ['nullable', 'exists:appointments,id'],
            'type' => ['required', 'in:visit,note,prescription,exam,diagnosis'],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],

            // Vital Parameters (opzionali)
            'vital_parameters.pressure' => 'nullable|string|max:20',
            'vital_parameters.heart_rate' => 'nullable|numeric',
            'vital_parameters.temperature' => 'nullable|numeric',
            'vital_parameters.weight' => 'nullable|numeric',
            'vital_parameters.height' => 'nullable|numeric',

            // Prescriptions (opzionali)
            'prescriptions' => 'nullable|array',
            'prescriptions.*.drug_name' => 'required_with:prescriptions|string|max:255',
            'prescriptions.*.dosage' => 'nullable|string|max:255',
            'prescriptions.*.frequency' => 'nullable|string|max:255',
            'prescriptions.*.duration' => 'nullable|string|max:255',
            'prescriptions.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            // Campi principali
            'medical_record_id.required' => 'La cartella clinica è obbligatoria.',
            'medical_record_id.exists' => 'La cartella clinica selezionata non esiste.',
            'appointment_id.required' => 'L\'appuntamento è obbligatorio.',
            'appointment_id.exists' => 'L\'appuntamento selezionato non esiste.',
            'type.required' => 'Il tipo di visita è obbligatorio.',
            'title.required' => 'Il titolo della visita è obbligatorio.',
            'title.max' => 'Il titolo non può superare 255 caratteri.',
            'content.string' => 'Il contenuto deve essere una stringa.',

            // Parametri vitali
            'vital_parameters.pressure.max' => 'La pressione non può superare 20 caratteri.',
            'vital_parameters.heart_rate.numeric' => 'La frequenza cardiaca deve essere un numero.',
            'vital_parameters.temperature.numeric' => 'La temperatura deve essere un numero.',
            'vital_parameters.weight.numeric' => 'Il peso deve essere un numero.',
            'vital_parameters.height.numeric' => 'L\'altezza deve essere un numero.',

            // Prescrizioni
            'prescriptions.array' => 'Le prescrizioni devono essere un array.',
            'prescriptions.*.drug_name.required_with' => 'Il nome del farmaco è obbligatorio se viene inserita una prescrizione.',
            'prescriptions.*.drug_name.max' => 'Il nome del farmaco non può superare 255 caratteri.',
            'prescriptions.*.dosage.max' => 'Il dosaggio non può superare 255 caratteri.',
            'prescriptions.*.frequency.max' => 'La frequenza non può superare 255 caratteri.',
            'prescriptions.*.duration.max' => 'La durata non può superare 255 caratteri.',
            'prescriptions.*.notes.max' => 'Le note non possono superare 500 caratteri.',
        ];
    }
}
