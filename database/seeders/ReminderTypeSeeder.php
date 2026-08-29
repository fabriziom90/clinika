<?php

namespace Database\Seeders;

use App\Models\ReminderType;
use Illuminate\Database\Seeder;

class ReminderTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'SMS',
                'code' => 'sms',
                'subject' => null,
                'message' => '',
                'sent_before_value' => 24,
                'sent_before_unit' => 'hours',
            ],
            [
                'name' => 'Whatsapp',
                'code' => 'whatsapp',
                'subject' => null,
                'message' => '',
                'sent_before_value' => 24,
                'sent_before_unit' => 'hours',
            ],
            [
                'name' => 'Email',
                'code' => 'email',
                'subject' => null,
                'message' => '',
                'sent_before_value' => 24,
                'sent_before_unit' => 'hours',
            ],
        ];

        foreach ($types as $type) {
            ReminderType::create($type);
        }
    }
}
