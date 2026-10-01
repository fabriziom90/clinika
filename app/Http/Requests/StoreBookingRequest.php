<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clinic_id' => [
                'required',
                'integer',
                'exists:central.clinics,id',
            ],

            'doctor_id' => [
                'required',
                'integer',
            ],

            'service_id' => [
                'required',
                'integer',
            ],

            'date' => [
                'required',
                'date',
            ],

            'time' => [
                'required',
                'date_format:H:i',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'surname' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'first_visit' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'clinic_id.required' => 'Seleziona una clinica.',
            'clinic_id.integer' => 'La clinica selezionata non è valida.',
            'clinic_id.exists' => 'La clinica selezionata non esiste.',

            'doctor_id.required' => 'Seleziona un medico.',
            'doctor_id.integer' => 'Il medico selezionato non è valido.',

            'service_id.required' => 'Seleziona una prestazione.',
            'service_id.integer' => 'La prestazione selezionata non è valida.',

            'date.required' => 'Seleziona una data.',
            'date.date' => 'La data selezionata non è valida.',

            'time.required' => 'Seleziona un orario.',
            'time.date_format' => 'L\'orario selezionato non è valido.',

            'name.required' => 'Inserisci il nome.',
            'name.string' => 'Il nome non è valido.',
            'name.max' => 'Il nome non può superare i 255 caratteri.',

            'surname.required' => 'Inserisci il cognome.',
            'surname.string' => 'Il cognome non è valido.',
            'surname.max' => 'Il cognome non può superare i 255 caratteri.',

            'email.required' => 'Inserisci l\'indirizzo email.',
            'email.email' => 'Inserisci un indirizzo email valido.',
            'email.max' => 'L\'indirizzo email non può superare i 255 caratteri.',

            'phone.required' => 'Inserisci il numero di telefono.',
            'phone.string' => 'Il numero di telefono non è valido.',
            'phone.max' => 'Il numero di telefono non può superare i 30 caratteri.',

            'first_visit.required' => 'Indica se si tratta di una prima visita.',
            'first_visit.boolean' => 'Il valore della prima visita non è valido.',
        ];
    }
}
