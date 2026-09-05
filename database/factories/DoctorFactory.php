<?php

namespace Database\Factories;

use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class DoctorFactory extends Factory
{
    protected $model = Doctor::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'personal_code' => strtoupper($this->faker->bothify('??????##?##?###?')),
            'vat' => $this->faker->numerify('###########'),
            'birthday' => $this->faker->date(),
            'birth_city' => $this->faker->city(),
            'city' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'cap' => $this->faker->postcode(),
            'phone' => $this->faker->phoneNumber(),
            'pec' => $this->faker->unique()->safeEmail(),
            'genre' => $this->faker->randomElement(['M', 'F']),
        ];
    }
}
