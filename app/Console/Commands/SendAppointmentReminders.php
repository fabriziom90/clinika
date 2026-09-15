<?php

namespace App\Console\Commands;

use App\Enums\ReminderStatus;
use App\Models\AppointmentReminder;
use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use App\Services\ReminderSenderService;
use Illuminate\Console\Command;
use Throwable;

class SendAppointmentReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Invia i reminder degli appuntamenti programmati';

    public function __construct(
        protected ReminderSenderService $sender,
        protected TenantDatabaseService $tenantDatabaseService
    ) {
        parent::__construct();
    }

    public function handle()
    {
        $clinics = Clinic::where('active', true)->get();

        if ($clinics->isEmpty()) {
            $this->info('Nessuna clinica attiva trovata.');

            return Command::SUCCESS;
        }

        foreach ($clinics as $clinic) {
            $this->newLine();
            $this->info("Clinica: {$clinic->name}");

            try {
                $this->tenantDatabaseService->connect($clinic);

                $reminders = AppointmentReminder::with([
                    'appointment',
                    'patient',
                    'reminderType',
                ])
                    ->where(function ($query) {
                        $query->where('status', ReminderStatus::PENDING)
                            ->orWhere(function ($q) {
                                $q->where('status', ReminderStatus::FAILED)
                                    ->where('attempt', '<', 3);
                            });
                    })
                    ->where('scheduled_for', '<=', now())
                    ->get();

                $this->info("Reminder da elaborare: {$reminders->count()}");

                foreach ($reminders as $reminder) {
                    try {
                        $this->sender->send($reminder);

                        $this->info("  ✓ Reminder {$reminder->id} elaborato");
                    } catch (Throwable $e) {
                        $this->error("  ✗ Reminder {$reminder->id}: {$e->getMessage()}");

                        report($e);
                    }
                }
            } catch (Throwable $e) {
                $this->error("Errore nella clinica {$clinic->name}: {$e->getMessage()}");

                report($e);
            }
        }

        return Command::SUCCESS;
    }
}
