<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        $baseIds = $user->accessibleBaseIds();

        $equipment = Equipment::whereIn('base_id', $baseIds)->get();

        return response()->json($equipment);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreEquipmentRequest $request)
    {
        $user = auth()->user();

        $baseId = $request->base_id;

        // SECURITY CHECK
        if (!$user->accessibleBaseIds()->contains($baseId)) {
            abort(403, 'You cannot create equipment for this base');
        }

        $equipment = Equipment::create($request->validated());

        return response()->json($equipment, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth()->user();

        $equipment = Equipment::findOrFail($id);

        if (!$user->accessibleBaseIds()->contains($equipment->base_id)) {
            abort(403);
        }

        return response()->json($equipment);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = auth()->user();

        $equipment = Equipment::findOrFail($id);

        if (!$user->accessibleBaseIds()->contains($equipment->base_id)) {
            abort(403);
        }

        $equipment->update($request->validated());

        return response()->json($equipment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
