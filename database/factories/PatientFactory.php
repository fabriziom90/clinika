<?php

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->firstName(),
            'surname' => $this->faker->lastName(),
            'personal_code' => strtoupper($this->faker->bothify('??????##?##?###?')),
            'birthday' => $this->faker->date(),
            'birth_city' => $this->faker->city(),
            'city' => $this->faker->city(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'genre' => $this->faker->randomElement(['M', 'F']),
            'zip_code' => $this->faker->postcode(),
            // nationality_id volutamente omesso: nullable nella maggior parte dei flussi,
            // va valorizzato esplicitamente nel test se serve una FK valida.
        ];
    }
}
