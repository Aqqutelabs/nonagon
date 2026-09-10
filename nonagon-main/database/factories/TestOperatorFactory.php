<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class TestOperatorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'organization_id' => null, // pass when creating
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone_no' => $this->faker->phoneNumber(),
            'position' => 'Operator',
            'role' => 'operator',
            'email_verified_at' => now(),
            'password' => Hash::make('12345678'),
        ];
    }

    // public function forOrganization($organizationId)
    // {
    //     return $this->state(fn () => ['organization_id' => $organizationId]);
    // }
}
