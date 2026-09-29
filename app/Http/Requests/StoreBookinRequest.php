<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'clinic_id' => [
                'required',
                'integer',
                'exists:clinics,id',
            ],

            'doctor_id' => [
                'required',
                'integer',
            ],

            'service_id' => [
                'required',
                'integer',
            ],

            'date' => [
                'required',
                'date',
            ],

            'time' => [
                'required',
                'date_format:H:i',
            ],

            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'surname' => [
                'required',
                'string',
                'max:255',
            ],

            'email' => [
                'required',
                'email',
                'max:255',
            ],

            'phone' => [
                'required',
                'string',
                'max:30',
            ],

            'first_visit' => [
                'required',
                'boolean',
            ],
        ];
    }
}
