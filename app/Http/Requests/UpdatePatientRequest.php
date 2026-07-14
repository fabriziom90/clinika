<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'name' => 'required',
            'surname' => 'required',
            'personal_code' => 'required|string|size:16',
            'birthday' => 'required|date',
            'birth_city' => 'required|string|max:30',
            'city' => 'required|string|max:30',
            'zip_code' => 'required|string|max:10',
            'address' => 'required|string|max:70',
            'phone' => 'required|string|max:15',
            'email' => 'required|string|max:70',
            'genre' => 'required',
            'nationality_id' => 'required',
            'reminder_types' => ['nullable', 'array'],
            'reminder_types.*' => [
                'exists:reminder_types,id',
            ],
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Il nome è obbligatorio',
            'surname.required' => 'Il cognome è obbligatorio',
            'personal_code.required' => 'Il codice fiscale è obbligatorio',
            'personal_code.size' => 'Il codice fiscale deve essere di 16 caratteri',
            'birthday.date' => 'La data di nascita deve essere in un formato valido',
            'birth_city.required' => 'La città di nascita è obbligatoria',
            'birth_city.max' => 'La città di nascita deve essere al massimo di :max caratteri',
            'city.required' => 'La città di nascita è obbligatoria',
            'city.max' => 'La città di nascita deve essere al massimo di :max caratteri',
            'zip_code.required' => 'Il cap è obbligatoria',
            'zip_code.max' => 'Il cap deve essere al massimo di :max caratteri',
            'address.required' => 'L\'indirizzo è obbligatorio',
            'address.max' => 'L\'indirizzo deve essere al massimo di :max caratteri',
            'phone.required' => 'Il numero di telefono è obbligatorio',
            'phone.max' => 'Il numero di telefono deve essere al massimo di :max caratteri',
            'email.required' => 'L\'indirizzo email p obbligatorio',
            'email.max' => 'L\'indirizzo email deve essere al massimo di :max caratteri',
            'genre.required' => 'Il genere dell\'utente è obbligatorio',
            'nationality_id.required' => 'La nazionalità è obbligatoria',
        ];
    }
}
