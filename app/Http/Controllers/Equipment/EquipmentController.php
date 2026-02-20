<?php

namespace App\Http\Controllers\Equipment;

use App\Constants\Features;
use App\Helpers\AccessHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Equipment;
use App\Models\EquipmentDepreciationProfile;
use Illuminate\Http\Request;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = auth()->user();

        if ($user->role === 'owner') {
            return Equipment::where('organization_id', $user->organization_id)->get();
        }

        return Equipment::where('organization_id', $user->organization_id)
            ->where(function ($q) use ($user) {
                $q->whereIn('base_id', AccessHelper::baseIds($user))
                    ->orWhereIn('site_id', AccessHelper::siteIds($user))
                    ->orWhereIn('plant_id', AccessHelper::plantIds($user))
                    ->orWhereIn('unit_id', AccessHelper::unitIds($user));
            })
            ->get();
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
        $org = $user->organization;

        $limit = $org->featureLimit(Features::EQUIPMENT_LIMIT); // revisit constants

        if ($limit !== null) {
            $count = Equipment::where('organization_id', $org->id)->count();

            if ($count >= $limit) {
                abort(403, 'Equipment limit reached');
            }
        }

        $validated = $request->validated();

        // Check access to location
        $fakeEquipment = new Equipment($validated);

        if (! AccessHelper::canAccessEquipment($user, $fakeEquipment)) {
            abort(404);
        }

        // create equipment
        $equipment = Equipment::create([
            ...$validated,
            'organization_id' => $user->organization_id,
        ]);

        // Create Depreciation Profile
        $depData = request()->validate([
            'method' => 'nullable|string',
            'acquisition_cost' => 'nullable|numeric',
            'acquisition_date' => 'nullable|date',
            'useful_life_years' => 'nullable|numeric',
            'salvage_value' => 'nullable|numeric',
            'start_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        // Create depreciation profile
        EquipmentDepreciationProfile::create([
            'equipment_id' => $equipment->id,
            'method' => $depData['method'] ?? 'straight_line',
            'acquisition_cost' => $depData['acquisition_cost'] ?? null,
            'acquisition_date' => $depData['acquisition_date'] ?? null,
            'useful_life_years' => $depData['useful_life_years'] ?? null,
            'salvage_value' => $depData['salvage_value'] ?? null,
            'start_date' => $depData['start_date'] ?? null,
            'notes' => $depData['notes'] ?? null,
        ]);

        return back()->with('success', 'Equipment created successfully');
    }


    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = auth()->user();
        $equipment = Equipment::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        if (!AccessHelper::canAccessEquipment($user, $equipment)) {
            abort(404);
        }

        return $equipment;
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
    public function update(StoreEquipmentRequest $request, $id)
    {
        $user = auth()->user();

        $equipment = Equipment::where('organization_id', $user->organization_id)
            ->findOrFail($id);

        if (!AccessHelper::canAccessEquipment($user, $equipment)) {
            abort(404);
        }

        $equipment->update($request->validated());

        return $equipment;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    // Equipment value cards
    public function purchaseValue(Equipment $equipment)
    {
        $depreciation = $equipment->depreciationProfile;
        $value = $depreciation->acquisition_cost;

        return $value;
    }

    public function activeYears()
    {

    }

    public function currentValue(Equipment $equipment)
    {
        $value = $equipment->value;
        $depreciation = $equipment->depreciationProfile;
        $acquisition_cost = $depreciation->acquisition_cost;

        $bookValue = $value->current_book_value;
        $marketValue = $value->current_market_value;

        if (!$bookValue) {
            $percentChange = ($marketValue - $acquisition_cost) / ($acquisition_cost * 100);
        }

        $percentChange = ($bookValue - $acquisition_cost) / ($acquisition_cost * 100);

        return $percentChange;
    }
}
