<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('+1 day', '+10 days');
        $end = (clone $start)->modify('+30 minutes');

        return [
            'patient_id' => Patient::factory(),
            'doctor_id' => Doctor::factory(),
            'start_time' => $start,
            'end_time' => $end,
            'duration_minutes' => 30,
            'status' => 'scheduled',
        ];
    }
}
