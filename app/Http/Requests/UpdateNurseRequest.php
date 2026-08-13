<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNurseRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required',
            'surname' => 'required',
            'personal_code' => 'required|string|size:16',
            'vat' => 'required|string|size:11',
            'birthday' => 'required|date',
            'birth_city' => 'required|string|max:30',
            'city' => 'required|string|max:30',
            'address' => 'required|string|max:70',
            'zip_code' => 'required|string|max:7',
            'phone' => 'required|string|max:15',
            'pec' => 'required|string|max:70',
            'genre' => 'required',
            'email' => [
                'required',
                'string',
                'max:70',
                function ($attribute, $value, $fail) {
                    $emailHash = hash('sha256', mb_strtolower(trim($value)));

                    $exists = \App\Models\User::where('email_hash', $emailHash)
                        ->where('id', '!=', $this->route('nurse')->user_id)
                        ->exists();

                    if ($exists) {
                        $fail('È già presente un utente con questo indirizzo email');
                    }
                },
            ],
            'nationality_id' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Il nome è obbligatorio',
            'surname.required' => 'Il cognome è obbligatorio',
            'personal_code.required' => 'Il codice fiscale è obbligatorio',
            'personal_code.size' => 'Il codice fiscale deve essere di 16 caratteri',
            'vat.required' => 'La partita iva è obbligatoria',
            'vat.size' => 'La partita iva deve essere di 13 caratteri',
            'birthday.required' => 'La data di nascita è obbligatorio',
            'birthday.date' => 'La data di nascita deve essere in un formato valido',
            'birth_city.required' => 'La città di nascita è obbligatoria',
            'birth_city.max' => 'La città di nascita deve essere al massimo di :max caratteri',
            'city.required' => 'La città di nascita è obbligatoria',
            'city.max' => 'La città di nascita deve essere al massimo di :max caratteri',
            'address.required' => 'L\'indirizzo è obbligatorio',
            'address.max' => 'L\'indirizzo deve essere al massimo di :max caratteri',
            'zip_code.required' => 'Il CAP è obbligatorio',
            'zip_code.max' => 'Il CAP deve essere al massimo di :max caratteri',
            'phone.required' => 'Il numero di telefono è obbligatorio',
            'phone.max' => 'Il numero di telefono deve essere al massimo di :max caratteri',
            'pec.required' => 'La PEC è obbligatoria',
            'pec.max' => 'La PEC deve essere al massimo di :max caratteri',
            'genre.required' => 'Il genere dell\'utente è obbligatorio',
            'email.required' => 'L\'indirizzo email è obbligatorio',
            'email.max' => 'L\'indirizzo email deve essere al massimo di :max caratteri',
            'email.unique' => 'È già presente un utente con questo indirizzo email',
            'nationality_id' => 'La nazionalità è obbligatoria',
        ];
    }
}
