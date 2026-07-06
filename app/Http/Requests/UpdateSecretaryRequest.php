<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSecretaryRequest extends FormRequest
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
            'zip_code' => 'required|digits:5',
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|string|max:70',
            'personal_code' => [
                'required',
                'string',
                'max:16',
                Rule::unique('secretaries', 'personal_code')
                    ->ignore($this->secretary),
            ],

            'birthday' => [
                'required',
                'date',
            ],

            'birth_city' => [
                'required',
                'string',
                'max:255',
            ],

            'nationality_id' => [
                'required',
                'exists:nationalities,id',
            ],

            'city' => [
                'required',
                'string',
                'max:255',
            ],

            'address' => [
                'required',
                'string',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'genre' => [
                'required',
                'string',
                'max:50',
            ],

            'employee_code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('secretaries', 'employee_code')
                    ->ignore($this->secretary),
            ],

            'notes' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'zip_code.required' => 'Il CAP è obbligatorio',
            'zip_code.digits' => 'Il CAP deve essere composto da 5 cifre.',
            'name.required' => 'Il nome è obbligatorio',
            'surname.required' => 'Il cognome è obbligatorio',
            'personal_code.required' => 'Il codice fiscale è obbligatorio',
            'personal_code.size' => 'Il codice fiscale deve essere di :size caratteri',
            'vat.required' => 'La partita iva è obbligatoria',
            'vat.size' => 'La partita iva deve essere di :size caratteri',
            'birthday.required' => 'La data di nascita è obbligatorio',
            'birthday.date' => 'La data di nascita deve essere in un formato valido',
            'birth_city.required' => 'La città di nascita è obbligatoria',
            'birth_city.max' => 'La città di nascita deve essere al massimo di :max caratteri',
            'city.required' => 'La città di nascita è obbligatoria',
            'city.max' => 'La città di nascita deve essere al massimo di :max caratteri',
            'address.required' => 'L\'indirizzo è obbligatorio',
            'address.max' => 'L\'indirizzo deve essere al massimo di :max caratteri',
            'phone.required' => 'Il numero di telefono è obbligatorio',
            'phone.max' => 'Il numero di telefono deve essere al massimo di :max caratteri',
            'email.required' => 'L\'indirizzo email p obbligatorio',
            'email.max' => 'L\'indirizzo email deve essere al massimo di :max caratteri',
            'genre.required' => 'Il genere dell\'utente è obbligatorio',
            'pec.required' => 'La pec è obbligatoria',
            'nationality_id' => 'La nazionalità è obbligatoria',
            'specialty_id' => 'La specializzazione è obbligatoria',
        ];
    }
}
