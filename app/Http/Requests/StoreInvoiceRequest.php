<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
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
            'appointment_id' => ['required', 'exists:appointments,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'patient_id' => ['required', 'exists:patients,id'],

            'date' => ['required', 'date'],

            'full_name' => ['required', 'string', 'max:255'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'zip_code' => ['nullable', 'string', 'max:10'],

            'description' => ['nullable', 'string'],

            'subtotal' => ['required', 'numeric', 'min:0'],
            'vat_amount' => ['required', 'numeric', 'min:0'],
            'stamp_duty' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['required', 'numeric', 'min:0'],
            'total' => ['required', 'numeric', 'min:0'],
            'amount' => ['required', 'numeric', 'min:0'],

            'items' => ['required', 'array', 'min:1'],

            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.description' => ['required', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'numeric', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.vat_percentage' => ['required', 'numeric', 'min:0'],
            'items.*.total' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'appointment_id.required' => 'L\'appuntamento è obbligatorio.',
            'appointment_id.exists' => 'L\'appuntamento selezionato non esiste.',

            'doctor_id.required' => 'Il medico è obbligatorio.',
            'doctor_id.exists' => 'Il medico selezionato non esiste.',

            'patient_id.required' => 'Il paziente è obbligatorio.',
            'patient_id.exists' => 'Il paziente selezionato non esiste.',

            'date.required' => 'La data della fattura è obbligatoria.',
            'date.date' => 'La data della fattura non è valida.',

            'full_name.required' => 'L\'intestatario è obbligatorio.',

            'subtotal.required' => 'Il subtotale è obbligatorio.',
            'vat_amount.required' => 'L\'importo IVA è obbligatorio.',
            'stamp_duty.required' => 'L\'importo del bollo è obbligatorio.',
            'discount_amount.required' => 'Lo sconto è obbligatorio.',
            'total.required' => 'Il totale è obbligatorio.',
            'amount.required' => 'L\'importo da pagare è obbligatorio.',

            'items.required' => 'Inserire almeno una voce in fattura.',
            'items.min' => 'Inserire almeno una voce in fattura.',

            'items.*.description.required' => 'La descrizione della voce è obbligatoria.',
            'items.*.quantity.required' => 'La quantità è obbligatoria.',
            'items.*.quantity.min' => 'La quantità deve essere almeno 1.',

            'items.*.unit_price.required' => 'Il prezzo unitario è obbligatorio.',
            'items.*.vat_percentage.required' => 'La percentuale IVA è obbligatoria.',
            'items.*.total.required' => 'Il totale della voce è obbligatorio.',
        ];
    }
}
