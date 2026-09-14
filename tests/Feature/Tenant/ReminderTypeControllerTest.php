<?php

namespace Tests\Feature\Tenant;

use App\Models\ReminderType;
use App\Models\User;
use Tests\TenantTestCase;

class ReminderTypeControllerTest extends TenantTestCase
{
    private function host(): string
    {
        return $this->clinic->slug.'.clinika.test';
    }

    private function url(string $routeName, array $params = []): string
    {
        return 'http://'.$this->host().route($routeName, $params, false);
    }

    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Promemoria 2 giorni prima',
            'subject' => 'Promemoria appuntamento',
            'message' => 'Ti aspettiamo tra 2 giorni.',
            'sent_before_value' => 2,
            'sent_before_unit' => 'days',
            'active' => true,
        ], $overrides);
    }

    public function test_user_without_view_permission_cannot_access_reminder_types(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get($this->url('admin.reminder-types.index'))
            ->assertForbidden();
    }

    public function test_user_with_view_permission_can_access_reminder_types(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reminder-type.view');

        $this->actingAs($user)
            ->get($this->url('admin.reminder-types.index'))
            ->assertSuccessful();
    }

    public function test_user_without_create_permission_cannot_create_reminder_type(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post($this->url('admin.reminder-types.store'), $this->validPayload())
            ->assertForbidden();

        $this->assertDatabaseMissing('reminder_types', ['name' => 'Promemoria 2 giorni prima'], 'tenant');
    }

    public function test_user_with_create_permission_can_create_reminder_type(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reminder-type.create');

        $this->actingAs($user)
            ->post($this->url('admin.reminder-types.store'), $this->validPayload())
            ->assertRedirect();

        $this->assertDatabaseHas('reminder_types', ['name' => 'Promemoria 2 giorni prima'], 'tenant');
    }

    public function test_user_without_update_permission_cannot_update_reminder_type(): void
    {
        $user = User::factory()->create();
        $reminderType = ReminderType::create(array_merge($this->validPayload(), ['code' => 'test-code']));

        $this->actingAs($user)
            ->put($this->url('admin.reminder-types.update', ['reminder_type' => $reminderType->id]), $this->validPayload(['name' => 'Nome modificato']))
            ->assertForbidden();

        $this->assertDatabaseHas('reminder_types', ['id' => $reminderType->id, 'name' => 'Promemoria 2 giorni prima'], 'tenant');
    }

    public function test_user_with_update_permission_can_update_reminder_type(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reminder-type.update');
        $reminderType = ReminderType::create(array_merge($this->validPayload(), ['code' => 'test-code']));

        $this->actingAs($user)
            ->put($this->url('admin.reminder-types.update', ['reminder_type' => $reminderType->id]), $this->validPayload(['name' => 'Nome modificato']))
            ->assertRedirect();

        $this->assertDatabaseHas('reminder_types', ['id' => $reminderType->id, 'name' => 'Nome modificato'], 'tenant');
    }

    public function test_user_without_delete_permission_cannot_delete_reminder_type(): void
    {
        $user = User::factory()->create();
        $reminderType = ReminderType::create(array_merge($this->validPayload(), ['code' => 'test-code']));

        $this->actingAs($user)
            ->delete($this->url('admin.reminder-types.destroy', ['reminder_type' => $reminderType->id]))
            ->assertForbidden();

        $this->assertDatabaseHas('reminder_types', ['id' => $reminderType->id], 'tenant');
    }

    public function test_user_with_delete_permission_can_delete_reminder_type(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('reminder-type.delete');
        $reminderType = ReminderType::create(array_merge($this->validPayload(), ['code' => 'test-code']));

        $this->actingAs($user)
            ->delete($this->url('admin.reminder-types.destroy', ['reminder_type' => $reminderType->id]))
            ->assertRedirect();

        $this->assertDatabaseMissing('reminder_types', ['id' => $reminderType->id], 'tenant');
    }
}
