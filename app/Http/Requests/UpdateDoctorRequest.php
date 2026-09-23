<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDoctorRequest extends FormRequest
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
            'vat' => 'required|string|size:11',
            'birthday' => 'required|date',
            'birth_city' => 'required|string|max:30',
            'city' => 'required|string|max:30',
            'address' => 'required|string|max:70',
            'phone' => 'required|string|max:15',
            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) {
                    $emailHash = hash('sha256', mb_strtolower(trim($value)));

                    if (\App\Models\User::where('email_hash', $emailHash)
                        ->where('id', '!=', $this->route('doctor')->user_id)
                        ->exists()) {
                        $fail('È già presente un utente con questo indirizzo email');
                    }
                },
            ],
            'genre' => 'required',
            'pec' => 'required',
            'specialty_id' => 'required',
            'nationality_id' => 'required',
            'services' => 'required|array',
            'services.*.service_id' => 'required|exists:services,id',
            'services.*.price' => 'required|numeric|min:0',
            'services.*.duration' => 'required|numeric|min:1',
            'services.*.compensation_type' => [
                'required',
                Rule::in(['percentage', 'fixed']),
            ],
            'services.*.compensation_value' => 'required|numeric|min:0',
            'services.*.active' => 'required|boolean',
            'zip_code' => 'required|string|max:7',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->input('services', []) as $index => $service) {
                $type = $service['compensation_type'] ?? null;
                $value = isset($service['compensation_value'])
                    ? (float) $service['compensation_value']
                    : null;
                $price = isset($service['price'])
                    ? (float) $service['price']
                    : null;

                if ($value === null || $price === null) {
                    continue;
                }

                if ($type === 'percentage' && $value > 100) {
                    $validator->errors()->add(
                        "services.{$index}.compensation_value",
                        'Il compenso percentuale non può essere superiore al 100%.'
                    );
                }

                if ($type === 'fixed' && $value > $price) {
                    $validator->errors()->add(
                        "services.{$index}.compensation_value",
                        'Il compenso non può essere superiore al prezzo della prestazione.'
                    );
                }
            }
        });
    }

    public function messages()
    {
        return [
            'name.required' => 'Il nome è obbligatorio',
            'surname.required' => 'Il cognome è obbligatorio',
            'personal_code.required' => 'Il codice fiscale è obbligatorio',
            'personal_code.size' => 'Il codice fiscale deve essere di :size caratteri',
            'vat.required' => 'La partita iva è obbligatoria',
            'vat.size' => 'La partita iva deve essere di :size caratteri',
            'zip_code.required' => 'Il CAP è obbligatorio',
            'zip_code.max' => 'Il CAP deve essere al massimo di :max caratteri',
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

            'services.*.service_id.required' => 'La prestazione è obbligatoria.',
            'services.*.service_id.exists' => 'La prestazione selezionata non esiste.',
            'services.*.price.required' => 'Il prezzo della prestazione è obbligatorio.',
            'services.*.price.numeric' => 'Il prezzo della prestazione deve essere numerico.',
            'services.*.price.min' => 'Il prezzo della prestazione non può essere negativo.',
            'services.*.duration.required' => 'La durata della prestazione è obbligatoria.',
            'services.*.duration.numeric' => 'La durata della prestazione deve essere numerica.',
            'services.*.duration.min' => 'La durata della prestazione deve essere maggiore di zero.',
            'services.*.compensation_type.required' => 'La tipologia del compenso è obbligatoria.',
            'services.*.compensation_type.in' => 'La tipologia del compenso non è valida.',
            'services.*.compensation_value.required' => 'Il valore del compenso è obbligatorio.',
            'services.*.compensation_value.numeric' => 'Il valore del compenso deve essere numerico.',
            'services.*.compensation_value.min' => 'Il valore del compenso non può essere negativo.',
            'services.*.active.required' => 'Lo stato della prestazione è obbligatorio.',
        ];
    }
}
