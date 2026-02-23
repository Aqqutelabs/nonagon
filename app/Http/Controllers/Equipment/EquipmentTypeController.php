<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Models\EquipmentType;
use Illuminate\Http\Request;

class EquipmentTypeController extends Controller
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
            'equipment_subcategory_id' => 'required|uuid|exists:equipment_subcategories,id',
            'description' => 'nullable|string'
        ]);

        $data['organization_id'] = $user->organization_id;

        $type = EquipmentType::create($data);

        return response()->json($type, 201);
    }

    public function index()
    {
        return EquipmentType::with('subcategory')->get();
    }
}
