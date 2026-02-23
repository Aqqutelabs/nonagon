<?php

namespace App\Helpers;

use App\Models\Unit;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Base;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccessHelper
{
    public static function canAccessEquipment($user, $equipment): bool
    {
        // Org owner/admin → full access
        if (in_array($user->role, ['owner', 'admin'])) {
            return true;
        }

        // Direct matches
        if (
            in_array($equipment->base_id, self::baseIds($user)) ||
            in_array($equipment->site_id, self::siteIds($user)) ||
            in_array($equipment->plant_id, self::plantIds($user)) ||
            in_array($equipment->unit_id, self::unitIds($user))
        ) {
            return true;
        }

        return false;
    }

    public static function baseIds($user)
    {
        return DB::table('location_users')
            ->where('user_id', $user->id)
            ->where('location_type', 'base')
            ->pluck('location_id')
            ->toArray();
    }

    public static function siteIds($user)
    {
        return DB::table('location_users')
            ->where('user_id', $user->id)
            ->where('location_type', 'site')
            ->pluck('location_id')
            ->toArray();
    }

    public static function plantIds($user)
    {
        return DB::table('location_users')
            ->where('user_id', $user->id)
            ->where('location_type', 'plant')
            ->pluck('location_id')
            ->toArray();
    }

    public static function unitIds($user)
    {
        return DB::table('location_users')
            ->where('user_id', $user->id)
            ->where('location_type', 'unit')
            ->pluck('location_id')
            ->toArray();
    }
}
