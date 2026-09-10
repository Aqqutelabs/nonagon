<?php

namespace Database\Seeders;

use App\Models\Base;
use App\Models\Equipment;
use App\Models\EquipmentCategory;
use App\Models\EquipmentStatus;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EquipmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $organizationId = Organization::first()->id;
        $categories = EquipmentCategory::all();
        $statuses = EquipmentStatus::all();
        $bases = Base::with('sites.plants.units')->get();
        $operators = User::where('role', 'operator')->get();

        foreach ($bases as $base) {
            foreach ($base->sites as $site) {
                foreach ($site->plants as $plant) {
                    foreach ($plant->units as $unit) {
                        Equipment::factory()->create([
                            'organization_id' => $organizationId,
                            'base_id' => $base->id,
                            'site_id' => $site->id,
                            'plant_id' => $plant->id,
                            'unit_id' => $unit->id,
                            'equipment_category_id' => $categories->random()->id,
                            'equipment_status_id' => $statuses->random()->id,
                            //'equipment_subcategory_id' => $categories->random()->id,
                            //'equipment_type_id' => $categories->random()->id,
                            //'operator_id' => $operators->random()->id,
                        ]);
                    }
                }
            }
        }
    }
}
