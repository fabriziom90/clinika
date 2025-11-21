<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDrugRequest extends FormRequest
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
            'name' => 'required|max:120',
            'unit_price' => 'nullable|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/', // regex: max 2 decimal
        ];
    }

    public function messages(){
        return [
            'name.required' => 'Il nome del prodotto è obbligatorio',
            'name.max'  => 'Il nome del prodotto deve essere lungo al massimo :max caratteri',
            'unit_price.numeric' => 'Il prezzo unitario deve essere un valore numerico valido',
            'unit_price.regex' => 'Il prezzo unitario deve avere al massimo 2 valori decimali'
        ];
    }
}
