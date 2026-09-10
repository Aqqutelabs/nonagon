<?php

namespace App\Http\Controllers\Equipment;

use App\Constants\Features;
use App\Helpers\AccessHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEquipmentRequest;
use App\Models\Base;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentDepreciationProfile;
use App\Models\EquipmentStatus;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EquipmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    // public function index()
    // {
    //     $user = auth()->user();

    //     if ($user->role === 'owner') {
    //         return Equipment::where('organization_id', $user->organization_id)->get();
    //     }

    //     return Equipment::where('organization_id', $user->organization_id)
    //         ->where(function ($q) use ($user) {
    //             $q->whereIn('base_id', AccessHelper::baseIds($user))
    //                 ->orWhereIn('site_id', AccessHelper::siteIds($user))
    //                 ->orWhereIn('plant_id', AccessHelper::plantIds($user))
    //                 ->orWhereIn('unit_id', AccessHelper::unitIds($user));
    //         })
    //         ->get();
    // }

    public function index(Request $request)
    {
        $user = auth()->user();
        $organizationId = $user->organization_id;

        /*
    |--------------------------------------------------------------------------
    | Base Query
    |--------------------------------------------------------------------------
    */

        $query = Equipment::with([
            'status',
            'category',
            'base',
            'site',
            'plant',
            'unit'
        ])->where('organization_id', $organizationId);


        /*
    |--------------------------------------------------------------------------
    | LOCATION ACCESS CONTROL (NEW)
    |--------------------------------------------------------------------------
    */

        if ($user->role !== 'owner') {

            $locationAccess = DB::table('location_users')
                ->where('user_id', $user->id)
                ->where('organization_id', $organizationId)
                ->get();

            $baseIds = $locationAccess->where('location_type', 'base')->pluck('location_id');
            $siteIds = $locationAccess->where('location_type', 'site')->pluck('location_id');
            $plantIds = $locationAccess->where('location_type', 'plant')->pluck('location_id');
            $unitIds = $locationAccess->where('location_type', 'unit')->pluck('location_id');

            $query->where(function ($q) use ($baseIds, $siteIds, $plantIds, $unitIds) {

                if ($baseIds->isNotEmpty()) {
                    $q->orWhereIn('base_id', $baseIds);
                }

                if ($siteIds->isNotEmpty()) {
                    $q->orWhereIn('site_id', $siteIds);
                }

                if ($plantIds->isNotEmpty()) {
                    $q->orWhereIn('plant_id', $plantIds);
                }

                if ($unitIds->isNotEmpty()) {
                    $q->orWhereIn('unit_id', $unitIds);
                }
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

        // Search
        if ($request->filled('search')) {

            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('aid', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('model', 'like', "%{$search}%");
            });
        }

        // Status
        if ($request->filled('status')) {
            $query->where('equipment_status_id', $request->status);
        }

        // Category
        if ($request->filled('category')) {
            $query->where('equipment_category_id', $request->category);
        }

        // Unit
        if ($request->filled('unit')) {
            $query->where('unit_id', $request->unit);
        }

        // Critical Only
        if ($request->filled('critical')) {
            $query->whereHas('status', function ($q) {
                $q->where('name', 'Critical');
            });
        }

        /*
    |--------------------------------------------------------------------------
    | Get Equipments
    |--------------------------------------------------------------------------
    */

        $equipments = $query->latest()->get();


        /*
    |--------------------------------------------------------------------------
    | Dropdown Data
    |--------------------------------------------------------------------------
    */

        $categories = EquipmentCategory::where(function ($query) use ($organizationId) {
            $query->where('is_global', true)
                ->orWhere('organization_id', $organizationId);
        })->orderBy('name')->get();


        $statuses = EquipmentStatus::where(function ($query) use ($organizationId) {
            $query->whereNull('organization_id')
                ->orWhere('organization_id', $organizationId);
        })->orderBy('name')->get();


        /*
    |--------------------------------------------------------------------------
    | Location Tree
    |--------------------------------------------------------------------------
    */

        $bases = Base::where('organization_id', $organizationId)
            ->with(['sites.plants.units' => function ($query) {
                $query->withCount('equipments');
            }])
            ->get();


        /*
    |--------------------------------------------------------------------------
    | Filter Location Tree for Operators
    |--------------------------------------------------------------------------
    */

        if ($user->role !== 'owner') {

            $bases = $bases->filter(function ($base) use ($baseIds, $siteIds, $plantIds, $unitIds) {

                if ($baseIds->contains($base->id)) {
                    return true;
                }

                $base->sites = $base->sites->filter(function ($site) use ($siteIds, $plantIds, $unitIds) {

                    if ($siteIds->contains($site->id)) {
                        return true;
                    }

                    $site->plants = $site->plants->filter(function ($plant) use ($plantIds, $unitIds) {

                        if ($plantIds->contains($plant->id)) {
                            return true;
                        }

                        $plant->units = $plant->units->filter(function ($unit) use ($unitIds) {
                            return $unitIds->contains($unit->id);
                        });

                        return $plant->units->isNotEmpty();
                    });

                    return $site->plants->isNotEmpty();
                });

                return $base->sites->isNotEmpty();
            });
        }


        /*
    |--------------------------------------------------------------------------
    | Stats
    |--------------------------------------------------------------------------
    */

        $totalAssets = $equipments->count();

        $maintenanceStatus = $statuses->where('name', 'Maintenance')->first();

        $underMaintenance = $maintenanceStatus
            ? $equipments->where('equipment_status_id', $maintenanceStatus->id)->count()
            : 0;


        $totalOperators = User::where('organization_id', $organizationId)
            ->where('role', 'operator')
            ->count();


        return view('admin.equipment', compact(
            'equipments',
            'categories',
            'statuses',
            'bases',
            'totalAssets',
            'underMaintenance',
            'totalOperators'
        ));
    }

    public function search(Request $request) // ilike for postgres
    {
        $user = auth()->user();
        $orgId = $user->organization_id;
        $query = $request->input('q');

        $equipments = Equipment::with(['category', 'status'])
            ->where('organization_id', $orgId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'ilike', "%{$query}%")
                    ->orWhere('aid', 'ilike', "%{$query}%")
                    ->orWhere('brand', 'ilike', "%{$query}%")
                    ->orWhere('model', 'ilike', "%{$query}%")
                    ->orWhereHas('category', fn($qc) => $qc->where('name', 'ilike', "%{$query}%"))
                    ->orWhereHas('operator', fn($qo) => $qo->where('name', 'ilike', "%{$query}%"));
            })
            ->get();

        // return JSON for frontend
        return response()->json($equipments);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create() {}

    /**
     * Store a newly created resource in storage.
     */
    // public function store(StoreEquipmentRequest $request)
    // {
    //     $user = auth()->user();
    //     $org = $user->organization;

    //     $limit = $org->featureLimit(Features::EQUIPMENT_LIMIT); // revisit constants

    //     if ($limit !== null) {
    //         $count = Equipment::where('organization_id', $org->id)->count();

    //         if ($count >= $limit) {
    //             abort(403, 'Equipment limit reached');
    //         }
    //     }

    //     $validated = $request->validated();

    //     // Check access to location
    //     $fakeEquipment = new Equipment($validated);

    //     if (! AccessHelper::canAccessEquipment($user, $fakeEquipment)) {
    //         abort(404);
    //     }

    //     // create equipment
    //     $equipment = Equipment::create([
    //         ...$validated,
    //         'organization_id' => $user->organization_id,
    //     ]);

    //     // Create Depreciation Profile
    //     $depData = request()->validate([
    //         'method' => 'nullable|string',
    //         'acquisition_cost' => 'nullable|numeric',
    //         'acquisition_date' => 'nullable|date',
    //         'useful_life_years' => 'nullable|numeric',
    //         'salvage_value' => 'nullable|numeric',
    //         'start_date' => 'nullable|date',
    //         'notes' => 'nullable|string',
    //     ]);

    //     // Create depreciation profile
    //     EquipmentDepreciationProfile::create([
    //         'equipment_id' => $equipment->id,
    //         'method' => $depData['method'] ?? 'straight_line',
    //         'acquisition_cost' => $depData['acquisition_cost'] ?? null,
    //         'acquisition_date' => $depData['acquisition_date'] ?? null,
    //         'useful_life_years' => $depData['useful_life_years'] ?? null,
    //         'salvage_value' => $depData['salvage_value'] ?? null,
    //         'start_date' => $depData['start_date'] ?? null,
    //         'notes' => $depData['notes'] ?? null,
    //     ]);

    //     return back()->with('success', 'Equipment created successfully');
    // }

    public function store(StoreEquipmentRequest $request)
    {
        //dd('store hit');
        $user = auth()->user();
        $organizationId = $user->organization_id;

        $validated = $request->validated();

        // Ensure selected base belongs to organization
        $base = Base::where('organization_id', $organizationId)
            ->findOrFail($validated['base_id']);

        // Validate hierarchy integrity
        $unit = Unit::with('plant.site.base')
            ->findOrFail($validated['unit_id']);

        if (
            $unit->plant->id != $validated['plant_id'] ||
            $unit->plant->site->id != $validated['site_id'] ||
            $unit->plant->site->base->id != $validated['base_id']
        ) {
            abort(403, 'Invalid location hierarchy.');
        }

        Equipment::create([
            ...$validated,
            'organization_id' => $organizationId,
        ]);

        return redirect()->route('equipments.index')
            ->with('success', 'Equipment created successfully');
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

    public function getEquipmentByOrg()
    {
        $user = auth()->user();
        $org = $user->organization;
        $equipment = Equipment::where('organization_id', $org->id)->get();

        return $equipment;
    }

    // Equipment value cards
    public function purchaseValue(Equipment $equipment)
    {
        $depreciation = $equipment->depreciationProfile;
        $value = $depreciation->acquisition_cost;

        return $value;
    }

    public function activeYears() {}

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
