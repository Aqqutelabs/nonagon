<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEquipmentRequest extends FormRequest
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
            'base_id' => ['required','uuid','exists:bases,id'],

            'eid' => ['nullable','string'],
            'reg_no' => ['nullable','string'],
            'model' => ['nullable','string'],
            'year_of_prod' => ['nullable','integer'],
            'description' => ['nullable','string'],
            'industry' => ['nullable','string'],
            'city' => ['nullable','string'],
            'last_maintenance' => ['nullable','string'],
        ];
    }
}
