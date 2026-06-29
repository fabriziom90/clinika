<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ChangeInvoiceStatusRequest extends FormRequest
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
            'status' => [
                'required',
                Rule::in(['draft', 'issued', 'paid', 'cancelled']),
            ],
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Lo stato della fattura è obbligatorio.',
            'status.in' => 'Lo stato selezionato non è valido. Gli stati disponibili sono bozza, emessa, pagata ed annullata',
        ];
    }
}
