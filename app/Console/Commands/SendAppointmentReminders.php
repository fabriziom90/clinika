<?php

namespace App\Console\Commands;

use App\Models\AppointmentReminder;
use App\Services\ReminderSenderService;
use Illuminate\Console\Command;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reminders:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Invia i reminder degli appuntamenti programmati';

    public function __construct(protected ReminderSenderService $sender)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminders = AppointmentReminder::with([
            'appointment',
            'patient',
            'reminderType',
        ])
            ->where('status', 'pending')
            ->where('scheduled_for', '<=', now())
            ->get();

        foreach ($reminders as $reminder) {
            $this->sender->send($reminder);

            $this->info("Reminder {$reminder->id} elaborato");
        }

        return Command::SUCCESS;
    }
}
