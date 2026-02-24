<?php

namespace Database\Factories;

use App\Models\Equipment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Equipment>
 */
class EquipmentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Equipment::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'aid' => 'AST-' . $this->faker->unique()->numberBetween(100, 999),
            'organization_id' => null,
            'base_id' => null,
            'site_id' => null,
            'plant_id' => null,
            'unit_id' => null,
            'equipment_category_id' => null,
            'equipment_subcategory_id' => null,
            'equipment_type_id' => null,
            'equipment_status_id' => null,
        ];
    }
}
