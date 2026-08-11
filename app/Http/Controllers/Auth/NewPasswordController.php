<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Connection\TenantDatabaseService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class NewPasswordController extends Controller
{
    public function __construct(
        private TenantDatabaseService $tenantDatabaseService
    ) {}

    /**
     * Display the password reset view.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $request->route('token'),
        ]);
    }

    /**
     * Handle an incoming new password request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $resetToken = DB::connection('tenant')
            ->table('password_reset_tokens')
            ->get()
            ->first(function ($record) use ($request) {
                return Hash::check($request->token, $record->token);
            });

        if (! $resetToken) {
            throw ValidationException::withMessages([
                'token' => ['Il link per il reset della password non è valido o è scaduto.'],
            ]);
        }

        if ($resetToken->created_at < now()->subMinutes(60)) {
            DB::connection('tenant')
                ->table('password_reset_tokens')
                ->where('user_id', $resetToken->user_id)
                ->delete();

            throw ValidationException::withMessages([
                'token' => ['Il link per il reset della password è scaduto.'],
            ]);
        }

        $user = User::findOrFail($resetToken->user_id);

        $user->forceFill([
            'password' => Hash::make($request->password),
            'remember_token' => Str::random(60),
        ])->save();

        event(new PasswordReset($user));

        DB::connection('tenant')
            ->table('password_reset_tokens')
            ->where('user_id', $user->id)
            ->delete();

        return redirect()
            ->route('login')
            ->with('status', 'Password modificata con successo.');
    }
}
