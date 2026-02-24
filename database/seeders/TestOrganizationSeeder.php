<?php

namespace Database\Seeders;

use App\Models\Base;
use App\Models\Organization;
use App\Models\Plant;
use App\Models\Site;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestOrganizationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create organization
        $organization = Organization::create([
            'name' => 'Test Industrial Ltd',
            'email' => 'testorg@example.com',
            'phone_no' => '08000000000',
            'address' => 'Lagos, Nigeria',
            'is_email_verified' => true,
            'status' => 'active',
        ]);

        // Create User(Owner)
        User::create([
            'organization_id' => $organization->id,
            'name' => 'Test Owner',
            'email' => 'owner@testorg.com',
            'phone_no' => '08011111111',
            'position' => 'Managing Director',
            'role' => 'owner',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'), // login password
        ]);

        // Operators (using factory)
        \App\Models\User::factory()
            ->count(5)
            ->create([
                'organization_id' => $organization->id,
                'role' => 'operator',
            ]);

        // Create Hierachy Locations
        for ($b = 1; $b <= 2; $b++) {

            $base = Base::create([

                'organization_id' => $organization->id,
                'name' => "Block $b",
            ]);

            for ($s = 1; $s <= 2; $s++) {

                $site = Site::create([

                    'base_id' => $base->id,
                    'organization_id' => $organization->id,
                    'name' => "Site {$b}.{$s}",
                ]);

                for ($p = 1; $p <= 2; $p++) {

                    $plant = Plant::create([

                        'site_id' => $site->id,
                        'base_id' => $base->id,
                        'organization_id' => $organization->id,
                        'name' => "Plant {$b}.{$s}.{$p}",
                    ]);

                    for ($u = 1; $u <= 2; $u++) {

                        Unit::create([

                            'plant_id' => $plant->id,
                            'base_id' => $base->id,
                            'site_id' => $site->id,
                            'organization_id' => $organization->id,
                            'name' => "Unit {$b}.{$s}.{$p}.{$u}",
                        ]);
                    }
                }
            }
        }
    }
}
