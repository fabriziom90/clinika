<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateInventoryProductRequest extends FormRequest
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
            'room_id' => ['nullable', 'exists:clinic_rooms,id'],
            'product_id' => ['nullable', 'exists:products,id'],
            'expiry_date' => ['required', 'date'],
            'units' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_id.exists' => 'La stanza selezionata non esiste.',
            'product_id.exists' => 'Il prodotto selezionato non esiste.',
            'expiry_date.required' => 'La data di scadenza è obbligatoria.',
            'expiry_date.date' => 'La data di scadenza non è valida.',
            'units.required' => 'Il numero di unità è obbligatorio.',
            'units.integer' => 'Il numero di unità deve essere un numero intero.',
            'units.min' => 'Il numero di unità deve essere almeno 1.',
        ];
    }
}
