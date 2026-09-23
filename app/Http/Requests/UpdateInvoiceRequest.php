<?php

namespace App\Http\Requests;

use App\Models\Doctor;
use Illuminate\Foundation\Http\FormRequest;

class UpdateInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

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

            'discount_amount' => [
                'required',
                'numeric',
                'min:0',
                'max:100',
            ],

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

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $doctor = Doctor::with([
                'services' => function ($query) {
                    $query->withPivot([
                        'price',
                        'duration_minutes',
                        'compensation_type',
                        'compensation_value',
                        'active',
                    ]);
                },
            ])->find($this->input('doctor_id'));

            if (! $doctor) {
                return;
            }

            $serviceIds = collect($this->input('items', []))
                ->pluck('service_id')
                ->filter()
                ->unique()
                ->values();

            if ($serviceIds->isEmpty()) {
                return;
            }

            $doctorServices = $doctor->services
                ->whereIn('id', $serviceIds)
                ->keyBy('id');

            $subtotal = 0;
            $maximumCompensation = 0;

            foreach ($this->input('items', []) as $item) {
                $quantity = (float) ($item['quantity'] ?? 0);
                $unitPrice = (float) ($item['unit_price'] ?? 0);

                $subtotal += $quantity * $unitPrice;

                $serviceId = $item['service_id'] ?? null;

                if (! $serviceId || ! $doctorServices->has($serviceId)) {
                    continue;
                }

                $service = $doctorServices->get($serviceId);

                $compensationType = $service->pivot->compensation_type;
                $compensationValue = (float) $service->pivot->compensation_value;

                if ($compensationType === 'percentage') {
                    $maximumCompensation +=
                        ($quantity * $unitPrice)
                        * ($compensationValue / 100);
                } else {
                    $maximumCompensation +=
                        $compensationValue * $quantity;
                }
            }

            if ($subtotal <= 0) {
                return;
            }

            $discountPercentage = (float) $this->input(
                'discount_amount',
                0
            );

            $discountAmount =
                $subtotal * ($discountPercentage / 100);

            $discountedSubtotal =
                $subtotal - $discountAmount;

            if ($discountedSubtotal < $maximumCompensation) {
                $validator->errors()->add(
                    'discount_amount',
                    'Lo sconto applicato è troppo elevato: non può ridurre l\'importo delle prestazioni al di sotto del compenso spettante al medico.'
                );
            }
        });
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
            'subtotal.numeric' => 'Il subtotale deve essere numerico.',
            'subtotal.min' => 'Il subtotale non può essere negativo.',

            'vat_amount.required' => 'L\'importo IVA è obbligatorio.',
            'vat_amount.numeric' => 'L\'importo IVA deve essere numerico.',
            'vat_amount.min' => 'L\'importo IVA non può essere negativo.',

            'stamp_duty.required' => 'L\'importo del bollo è obbligatorio.',
            'stamp_duty.numeric' => 'L\'importo del bollo deve essere numerico.',
            'stamp_duty.min' => 'L\'importo del bollo non può essere negativo.',

            'discount_amount.required' => 'Lo sconto è obbligatorio.',
            'discount_amount.numeric' => 'Lo sconto deve essere numerico.',
            'discount_amount.min' => 'Lo sconto non può essere negativo.',
            'discount_amount.max' => 'Lo sconto non può essere superiore al 100%.',

            'total.required' => 'Il totale è obbligatorio.',
            'total.numeric' => 'Il totale deve essere numerico.',
            'total.min' => 'Il totale non può essere negativo.',

            'amount.required' => 'L\'importo da pagare è obbligatorio.',
            'amount.numeric' => 'L\'importo da pagare deve essere numerico.',
            'amount.min' => 'L\'importo da pagare non può essere negativo.',

            'items.required' => 'Inserire almeno una voce in fattura.',
            'items.min' => 'Inserire almeno una voce in fattura.',

            'items.*.description.required' => 'La descrizione della voce è obbligatoria.',

            'items.*.quantity.required' => 'La quantità è obbligatoria.',

            'items.*.quantity.min' => 'La quantità deve essere almeno 1.',

            'items.*.unit_price.required' => 'Il prezzo unitario è obbligatorio.',

            'items.*.unit_price.numeric' => 'Il prezzo unitario deve essere numerico.',

            'items.*.unit_price.min' => 'Il prezzo unitario non può essere negativo.',

            'items.*.vat_percentage.required' => 'La percentuale IVA è obbligatoria.',

            'items.*.vat_percentage.numeric' => 'La percentuale IVA deve essere numerica.',

            'items.*.vat_percentage.min' => 'La percentuale IVA non può essere negativa.',

            'items.*.total.required' => 'Il totale della voce è obbligatorio.',

            'items.*.total.numeric' => 'Il totale della voce deve essere numerico.',

            'items.*.total.min' => 'Il totale della voce non può essere negativo.',
        ];
    }
}
