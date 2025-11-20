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
            'doctor_id'   => ['nullable', 'exists:doctors,id'],
            'nurse_id'    => ['nullable', 'exists:nurses,id'],
            'patient_id'  => ['required', 'exists:patients,id'],
            'title'       => ['required', 'string', 'max:255'],
            'start_time'  => ['required', 'date'],
            'duration'    => ['required', 'integer', 'min:1'],
            'notes'       => ['nullable', 'string'],
        ];
    }

    public function messages()
    {
        return [
            'doctor_id.exists'  => 'Il medico selezionato non esiste.',
            'nurse_id.exists'   => 'L’infermiere selezionato non esiste.',
            'patient_id.required'  => 'Il paziente è obbligatorio',
            'patient_id.exists'  => 'Il paziente selezionato non esiste.',
            'title.required'    => 'Il titolo dell’appuntamento è obbligatorio.',
            'title.string'      => 'Il titolo deve essere un testo valido.',
            'title.max'         => 'Il titolo non può superare i 255 caratteri.',

            'start_time.required' => 'La data e ora di inizio sono obbligatorie.',
            'start_time.date'     => 'La data di inizio non è valida.',

            'duration.required'   => 'La durata è obbligatoria.',
            'duration.integer'    => 'La durata deve essere un numero intero.',
            'duration.min'        => 'La durata deve essere almeno di 1 minuto.',

            'notes.string'        => 'Le note devono essere un testo valido.',
        ];
    }

}
