<?php

namespace App\Http\Controllers;

use App\Models\Base;
use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrganizationController extends Controller
{
    private function createOrg(array $data): Organization
    {
        return DB::transaction(function () use ($data){
            $organization = Organization::create($data);

            if (!$organization->has_bases) {
                Base::create([
                    'organization_id' => $organization->id,
                    'name' => 'Main'
                ]);
            }

            return $organization;
        });
    }

    public function store(Request $request)
    {
        return $this->createOrg($request->validated());
    }
}
