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
            'name' => 'required|string',
            'aid' => 'nullable|string',
            'type' => 'nullable|string',
            'status' => 'nullable|string',
            'reg_no' => 'nullable|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'emd' => 'nullable|string',
            'manufacture_year' => 'nullable|integer',
            'description' => 'nullable|string',
            'industry' => 'nullable|string',
            'city' => 'nullable|string',
            'last_maintenance' => 'nullable|string',

            'organization_id' => 'required|uuid',
            'base_id' => 'required|uuid',
            'site_id' => 'required|uuid',
            'plant_id' => 'required|uuid',
            'unit_id' => 'required|uuid',

            'equipment_category_id' => 'required|uuid|exists:equipment_categories,id',
            'equipment_subcategory_id' => 'required|uuid|exists:equipment_subcategories,id',
            'equipment_type_id' => 'required|uuid|exists:equipment_types,id',
            'equipment_status_id' => 'required|uuid|exists:equipment_statuses,id',
        ];
    }
}
