<?php

namespace App\Http\Controllers\Equipment;

use App\Http\Controllers\Controller;
use App\Models\EquipmentStatus;
use Illuminate\Http\Request;

class EquipmentStatusController extends Controller
{
    public function index()
    {
        return EquipmentStatus::where(
            'organization_id',
            auth()->user()->organization_id
        )->get();
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if (!in_array($user->role, ['owner', 'admin'])) {
            abort(403);
        }

        $data = $request->validate([
            'name' => 'required|string',
            'severity' => 'nullable|integer',
            'is_terminal' => 'required|boolean'
        ]);

        $data['organization_id'] = $user->organization_id;

        $status = EquipmentStatus::create($data);

        return response()->json($status, 201);
    }
}
