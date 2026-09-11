<?php

namespace Tests\Feature\Superadmin;

use App\Models\CentralUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * IMPORTANTE: prima della patch (middleware EnsureIsSuperadmin applicato al
 * gruppo di rotte "superadmin"), il flag is_superadmin su CentralUser non
 * viene controllato da nessuna parte lato server - è usato solo per
 * decorare l'interfaccia (HandleInertiaRequests). Qualunque riga valida
 * nella tabella central "users", autenticata sul guard superadmin, ha oggi
 * pieno accesso a tutte le rotte /superadmin/*, incluso is_superadmin=false.
 *
 * "test_central_user_with_is_superadmin_false_cannot_access_superadmin_area"
 * fallisce finché la patch non è applicata.
 */
class SuperadminAccessTest extends TestCase
{
    private function createCentralUser(bool $isSuperadmin): CentralUser
    {
        $user = new CentralUser([
            'name' => 'Test User',
            'email' => 'central-'.Str::lower(Str::random(8)).'@test.it',
            'password' => bcrypt('password'),
            'is_superadmin' => $isSuperadmin,
        ]);

        $user->save();

        return $user;
    }

    public function test_central_user_with_is_superadmin_true_can_access_superadmin_area(): void
    {
        $user = $this->createCentralUser(true);

        Auth::guard('superadmin')->setUser($user);

        $this->get(route('superadmin.admins.index'))
            ->assertSuccessful();
    }

    public function test_central_user_with_is_superadmin_false_cannot_access_superadmin_area(): void
    {
        $user = $this->createCentralUser(false);

        Auth::guard('superadmin')->setUser($user);

        $this->get(route('superadmin.admins.index'))
            ->assertForbidden();
    }

    public function test_central_user_with_is_superadmin_false_cannot_create_an_admin(): void
    {
        $user = $this->createCentralUser(false);

        Auth::guard('superadmin')->setUser($user);

        $this->get(route('superadmin.admins.create'))
            ->assertForbidden();
    }
}
