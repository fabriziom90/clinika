<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

class AuditLogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Audit::class);

        $query = Audit::with('user');

        if ($search = $request->input('search')) {
            $query->where('action', 'like', "%$search%")
                ->orWhere('auditable_type', 'like', "%$search%");
        }

        $logs = $query->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Logs/AuditLogs', [
            'logs' => $logs,
            'filters' => $request->only('search'),
        ]);
    }
}
