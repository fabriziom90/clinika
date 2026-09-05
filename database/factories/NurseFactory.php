<?php

namespace Database\Factories;

use App\Models\Nurse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseFactory extends Factory
{
    protected $model = Nurse::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'personal_code' => fake()->unique()->regexify('[A-Z]{6}[0-9]{2}[A-Z][0-9]{2}[A-Z][0-9]{3}[A-Z]'),
            'vat' => fake()->unique()->numerify('###########'),
            'birthday' => fake()->date(),
            'birth_city' => fake()->city(),
            'city' => fake()->city(),
            'address' => fake()->streetAddress(),
            'phone' => fake()->numerify('3#########'),
            'pec' => fake()->unique()->safeEmail(),
            'cap' => fake()->numerify('#####'),
            'genre' => fake()->randomElement(['M', 'F']),
            'nationality_id' => null,
        ];
    }
}
