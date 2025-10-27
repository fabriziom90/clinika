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
            'personal_code' => 'required|string|size:16',
            'vat'           => 'required|string|size:13',
            'birthday'      => 'required|date',
            'birth_city'    => 'required|string|max:30',
            'city'          => 'required|string|max:30',
            'address'       => 'required|string|max:70',
            'cap'           => 'required|string|max:5',
            'province'      => 'required|string|max:30',
            'phone'         => 'required|string|max:15',
            'email'         => 'required|string|max:70'
        ];
    }

    public function messages(){
        return [
            'personal_code.required'    => 'Il codice fiscale è obbligatorio',
            'personal_code.size'        => 'Il codice fiscale deve essere di 16 caratteri',
            'vat.required'              => 'La partita iva è obbligatoria',
            'vat.size'                  => 'La partita iva deve essere di 13 caratteri',
            'birthday.required'         => 'La data di nascita è obbligatorio',
            'birthday.date'             => 'La data di nascita deve essere in un formato valido', 
            'birth_city.required'       => 'La città di nascita è obbligatoria',
            'birth_city.max'            => 'La città di nascita deve essere al massimo di :max caratteri',
            'city.required'       => 'La città di nascita è obbligatoria',
            'city.max'            => 'La città di nascita deve essere al massimo di :max caratteri',
            'address.required'      => 'L\'indirizzo è obbligatorio',
            'address.max'           => 'L\'indirizzo deve essere al massimo di :max caratteri',
            'cap.required'          => 'Il cap è obbligatorio',
            'cap.max'               => 'Il cap deve avere al massimo di :max caratteri',
            'province.required'     => 'La provincia è obbligatorio',
            'province.max'          => 'La provincia deve avere al massimo :max caratteri',
            'phone.required'        => 'Il numero di telefono è obbligatorio',
            'phone.max'             => 'Il numero di telefono deve essere al massimo di :max caratteri',
            'email.required'        => 'L\'indirizzo email p obbligatorio',
            'email.max'             => 'L\'indirizzo email deve essere al massimo di :max caratteri' 
        ];
    }
}
