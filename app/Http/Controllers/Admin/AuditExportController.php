<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Audit;
use Illuminate\Http\Request;

class AuditExportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'auditable_type' => ['required', 'string'],
            'auditable_id' => ['nullable', 'integer'],
        ]);

        Audit::forceCreate([
            'user_id' => auth()->id(),
            'user_type' => get_class(auth()->user()),
            'event' => 'exported',
            'auditable_type' => $data['auditable_type'],
            'auditable_id' => $data['auditable_id'] ?? null,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'old_values' => [],
            'new_values' => [],
        ]);

        return back()->with([
            'success' => true,
        ]);
    }
}
