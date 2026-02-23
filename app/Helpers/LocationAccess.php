<?php

namespace App\Helpers;

use App\Models\Unit;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Base;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class LocationAccess
{


    public function accessibleUnitIds($user): array
    {
        // Admin gets everything
        if ($user->role === 'admin' || $user->role === 'owner') {
            return Unit::where('organization_id', $user->organization_id)
                ->pluck('id')
                ->toArray();
        }

        $assignments = DB::table('location_users')
            ->where('user_id', $user->id)
            ->get();

        $unitIds = collect();

        foreach ($assignments as $assign) {

            switch ($assign->location_type) {

                // UNIT LEVEL
                case 'unit':
                    $unitIds->push($assign->location_id);
                    break;

                // PLANT LEVEL → get all units in plant
                case 'plant':
                    $units = Unit::where('plant_id', $assign->location_id)
                        ->pluck('id');

                    $unitIds = $unitIds->merge($units);
                    break;

                // SITE LEVEL → get all plants → units
                case 'site':
                    $plantIds = Plant::where('site_id', $assign->location_id)
                        ->pluck('id');

                    $units = Unit::whereIn('plant_id', $plantIds)
                        ->pluck('id');

                    $unitIds = $unitIds->merge($units);
                    break;

                // BASE LEVEL → sites → plants → units
                case 'base':
                    $siteIds = Site::where('base_id', $assign->location_id)
                        ->pluck('id');

                    $plantIds = Plant::whereIn('site_id', $siteIds)
                        ->pluck('id');

                    $units = Unit::whereIn('plant_id', $plantIds)
                        ->pluck('id');

                    $unitIds = $unitIds->merge($units);
                    break;
            }
        }

        return $unitIds->unique()->values()->toArray();
    }

    public static function canAccessEquipment($user, $equipment): bool
    {
        if ($user->role === 'owner' || $user->role === 'admin') {
            return true;
        }

        $allowedUnitIds = self::accessibleUnitIds($user);

        return in_array($equipment->unit_id, $allowedUnitIds);
    }
}
