<?php

namespace Database\Factories;

use App\MaritalStatus;
use App\Models\ApplicationForm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationForm>
 */
class ApplicationFormFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'middle_name' => null,
            'birth_date' => fake()->dateTimeBetween('-70 years', '-18 years')->format('Y-m-d'),
            'email' => fake()->safeEmail(),
            'phone_numbers' => null,
            'marital_status' => MaritalStatus::Single,
            'about' => fake()->text(),
            'accepted_rules' => true,
        ];
    }
}
