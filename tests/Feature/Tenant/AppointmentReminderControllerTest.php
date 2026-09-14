<?php

namespace Tests\Feature\Tenant;

use App\Models\Appointment;
use App\Models\AppointmentReminder;
use App\Models\Patient;
use App\Models\ReminderType;
use App\Models\User;
use Tests\TenantTestCase;

class AppointmentReminderControllerTest extends TenantTestCase
{
    private function host(): string
    {
        return $this->clinic->slug.'.clinika.test';
    }

    private function url(string $routeName, array $params = []): string
    {
        return 'http://'.$this->host().route($routeName, $params, false);
    }

    private function createReminder(): AppointmentReminder
    {
        $patient = Patient::factory()->create();
        $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);
        $reminderType = ReminderType::create([
            'name' => 'Promemoria test',
            'code' => 'test-reminder',
            'message' => 'Messaggio di test.',
            'sent_before_value' => 1,
            'sent_before_unit' => 'days',
            'active' => true,
        ]);

        return AppointmentReminder::create([
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'reminder_type_id' => $reminderType->id,
            'scheduled_for' => now()->addDay(),
            'status' => 'pending',
        ]);
    }

    public function test_user_without_view_permission_cannot_access_reminders_index(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($this->url('admin.reminders.index'))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_reminders_index(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('appointment-reminder.view');

        $this->actingAs($user)
            ->get($this->url('admin.reminders.index'))
            ->assertSuccessful();
    }

    public function test_user_without_view_permission_cannot_show_a_reminder(): void
    {
        $user = User::factory()->create();
        $reminder = $this->createReminder();

        $this->actingAs($user)
            ->get($this->url('admin.reminders.show', ['reminder' => $reminder->id]))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_show_a_reminder(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('appointment-reminder.view');
        $reminder = $this->createReminder();

        $this->actingAs($user)
            ->get($this->url('admin.reminders.show', ['reminder' => $reminder->id]))
            ->assertSuccessful();
    }

    public function test_doctor_can_access_reminders_via_role_default_permission(): void
    {
        // RoleSeeder assegna appointment-reminder.view esplicitamente al medico.
        $doctorUser = User::factory()->create();
        $doctorUser->assignRole('doctor');

        $this->actingAs($doctorUser)
            ->get($this->url('admin.reminders.index'))
            ->assertSuccessful();
    }
}
