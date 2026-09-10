<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Models\EquipmentCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class EquipmentCategoryController extends Controller
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
            'description' => 'nullable|string',
        ]);

        $data['is_global'] = true;
        $data['organization_id'] = $user->organization_id;

        $category = EquipmentCategory::create($data);

        return response()->json($category, 201);
    }

    public function index()
    {
        return EquipmentCategory::all();
    }
}
