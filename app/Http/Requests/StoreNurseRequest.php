<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNurseRequest extends FormRequest
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
            'name' => 'required',
            'surname' => 'required',
            'personal_code' => 'required|string|size:16',
            'vat' => 'required|string|size:11',
            'birthday' => 'required|date',
            'birth_city' => 'required|string|max:30',
            'city' => 'required|string|max:30',
            'address' => 'required|string|max:70',
            'phone' => 'required|string|max:15',
            'email' => [
                'required',
                'string',
                'max:70',
                function ($attribute, $value, $fail) {
                    $emailHash = hash('sha256', mb_strtolower(trim($value)));

                    if (\App\Models\User::where('email_hash', $emailHash)->exists()) {
                        $fail('È già presente un utente con questo indirizzo email');
                    }
                }, ],
            'genre' => 'required',
            'nationality_id' => 'required',
            'pec' => 'required|string|max:70',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Il nome è obbligatorio',
            'surname.required' => 'Il cognome è obbligatorio',
            'personal_code.required' => 'Il codice fiscale è obbligatorio',
            'personal_code.size' => 'Il codice fiscale deve essere di :max caratteri',
            'vat.required' => 'La partita iva è obbligatoria',
            'vat.size' => 'La partita iva deve essere di :max caratteri',
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
            'nationality_id' => 'La nazionalità è obbligatoria',
            'pec.required' => 'La PEC è obbligatoria',
            'pec.max' => 'La PEC deve essere al massimo di :max caratteri',
        ];
    }
}
