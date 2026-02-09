<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'service_id' => ['required', 'exists:services,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'nurse_id' => ['nullable', 'exists:nurses,id'],
            'duration' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages()
    {
        return [
            'date.required' => 'La data dell’appuntamento è obbligatoria.',
            'date.date' => 'La data dell’appuntamento non è valida.',

            'start_time.required' => 'L’orario di inizio è obbligatorio.',
            'start_time.date' => 'L’orario di inizio non è valido.',

            'doctor_id.required' => 'Seleziona un medico.',
            'doctor_id.exists' => 'Il medico selezionato non è valido.',

            'service_id.required' => 'Seleziona una prestazione.',
            'service_id.exists' => 'La prestazione selezionata non è valida.',

            'patient_id.required' => 'Seleziona un paziente.',
            'patient_id.exists' => 'Il paziente selezionato non è valido.',

            'nurse_id.exists' => 'L’infermiere selezionato non è valido.',

            'duration.required' => 'La durata è obbligatoria.',
            'duration.integer' => 'La durata deve essere un numero.',
            'duration.min' => 'La durata deve essere di almeno 1 minuto.',

            'notes.string' => 'Le note devono essere un testo.',
            'notes.max' => 'Le note non possono superare i 2000 caratteri.',
        ];
    }
}
