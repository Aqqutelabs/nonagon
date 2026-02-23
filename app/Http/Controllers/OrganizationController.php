<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Organization;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationController extends Controller
{
    public function createOrg(array $data, $creator): Organization
    {

    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'phone_no' => 'required|string|max:255',
            'address' => 'required|string|max:255',
        ]);

        //$user = auth()->user();

        $organization = DB::transaction(function () use ($request) {

            // 1. Create Organization

            $organization = Organization::create([
                'id' => Str::uuid(),
                'name' => $request->name,
                'industry' => $request->industry,
            ]);


            // 2. Default Base

            $base = Base::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // 3. Default Site
            $site = Site::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // 4. Default Plant
            $plant = Plant::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'site_id' => $site->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            // 5. Default Unit
            $unit = Unit::create([
                'id' => Str::uuid(),
                'organization_id' => $organization->id,
                'base_id' => $base->id,
                'site_id' => $site->id,
                'plant_id' => $plant->id,
                'name' => 'Default',
                'is_default' => true,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 6. Attach creator as ORG ADMIN
            |--------------------------------------------------------------------------
            | This gives full access everywhere
            */
            // LocationUser::create([
            //     'user_id' => $user->id,
            //     'location_type' => 'organization',
            //     'location_id' => $organization->id,
            // ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Update user org_id
            |--------------------------------------------------------------------------
            */
            // $user->update([
            //     'organization_id' => $organization->id,
            //     'role' => 'admin'
            // ]);

            return $organization;
        });

        return response()->json([
            'message' => 'Organization created successfully',
            'data' => $organization
        ]);
    }
}
