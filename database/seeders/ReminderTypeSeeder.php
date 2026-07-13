<?php

namespace Database\Seeders;

use App\Models\ReminderType;
use Illuminate\Database\Seeder;

class ReminderTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'SMS',
                'code' => 'sms',
                'description' => '',
                'sent_before_value' => 24,
                'sent_before_unit' => 'hours',
            ],
            [
                'name' => 'Whatsapp',
                'code' => 'whatsapp',
                'description' => '',
                'sent_before_value' => 24,
                'sent_before_unit' => 'hours',
            ],
            [
                'name' => 'Email',
                'code' => 'email',
                'description' => '',
                'sent_before_value' => 24,
                'sent_before_unit' => 'hours',
            ],
        ];

        foreach ($types as $type) {
            $reminderType = ReminderType::create([
                'name' => $type['name'],
                'code' => $type['code'],
                'description' => $type['description'],
                'sent_before_value' => $type['sent_before_value'],
                'sent_before_unit' => $type['sent_before_unit'],
            ]);
        }
    }
}
