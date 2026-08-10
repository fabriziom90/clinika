<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use App\Models\CentralUser;
use App\Models\Clinic;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        protected TenantDatabaseService $tenantDatabaseService
    ) {}

    public function index()
    {
        $clinics = Clinic::on('central')
            ->withTrashed()
            ->get();

        $clinicData = [];
        $totalAdmins = 0;
        $totalUsers = 0;

        foreach ($clinics as $clinic) {
            $this->tenantDatabaseService->connect($clinic);

            $admins = \App\Models\User::role('admin')->count();
            $doctors = \App\Models\User::role('doctor')->count();
            $nurses = \App\Models\User::role('nurse')->count();
            $secretaries = \App\Models\User::role('secretary')->count();

            $clinicUsers = \App\Models\User::count();

            $totalAdmins += $admins;
            $totalUsers += $clinicUsers;

            $clinicData[] = [
                'id' => $clinic->id,
                'name' => $clinic->name,
                'slug' => $clinic->slug,
                'active' => ! $clinic->trashed(),
                'admins' => $admins,
                'doctors' => $doctors,
                'nurses' => $nurses,
                'secretaries' => $secretaries,
            ];
        }
        $audits = DB::connection('central')
            ->table('audits')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $centralUserIds = $audits
            ->pluck('user_id')
            ->filter()
            ->unique();

        $users = CentralUser::on('central')
            ->whereIn('id', $centralUserIds)
            ->get()
            ->keyBy('id');

        $recentActivities = $audits->map(function ($audit) use ($users) {
            $user = $audit->user_id ? $users->get($audit->user_id) : null;

            $userName = $user
                ? trim($user->name.' '.$user->surname)
                : 'Sistema';

            $type = match ($audit->event) {
                'created' => 'created',
                'updated' => 'updated',
                'deleted' => 'deleted',
                'login' => 'login',
                'login_failed' => 'warning',
                default => 'warning',
            };

            $icon = match ($audit->event) {
                'created' => 'fa-solid fa-plus',
                'updated' => 'fa-solid fa-pen',
                'deleted' => 'fa-solid fa-trash',
                'login' => 'fa-solid fa-right-to-bracket',
                'login_failed' => 'fa-solid fa-triangle-exclamation',
                default => 'fa-solid fa-circle-info',
            };

            $title = match ($audit->event) {
                'created' => 'Elemento creato',
                'updated' => 'Elemento modificato',
                'deleted' => 'Elemento eliminato',
                'login' => 'Accesso effettuato',
                'login_failed' => 'Tentativo di accesso fallito',
                default => ucfirst($audit->event),
            };

            return [
                'id' => $audit->id,
                'type' => $type,
                'icon' => $icon,
                'title' => $title,
                'description' => $userName,
                'created_at' => $audit->created_at,
            ];
        })->values();

        return Inertia::render('Superadmin/Dashboard', [
            'stats' => [
                'clinics' => $clinics->count(),
                'activeClinics' => $clinics->whereNull('deleted_at')->count(),
                'inactiveClinics' => $clinics->whereNotNull('deleted_at')->count(),
                'admins' => $totalAdmins,
                'users' => $totalUsers,
            ],

            'clinics' => $clinicData,

            'recentActivities' => $recentActivities,
        ]);
    }
}
