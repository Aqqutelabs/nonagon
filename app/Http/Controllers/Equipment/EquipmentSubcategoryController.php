<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Models\EquipmentSubcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EquipmentSubcategoryController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        if (!in_array($user->role, ['owner','admin'])) {
            abort(403);
        }

        // if (!$user->role != 'admin') {
        //     abort(403);
        // }

        $data = $request->validate([
            'name' => 'required|string',
            'equipment_category_id' => 'required|uuid|exists:equipment_categories,id',
            'description' => 'nullable|string',
            'photo_url' => 'nullable|string'
        ]);

        $data['organization_id'] = $user->organization_id;

        $subcategory = EquipmentSubcategory::create($data);

        return response()->json($subcategory, 201);
    }

    public function index()
    {
        return EquipmentSubcategory::with('category')->get();
    }
}
