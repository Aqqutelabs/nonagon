<?php

namespace Database\Seeders;

use App\Models\EquipmentStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = ['Operational', 'Maintenance', 'Critical'];

        foreach ($statuses as $status) {
            EquipmentStatus::firstOrCreate(['name' => $status]);
        }
    }
}
