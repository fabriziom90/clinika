<?php

namespace App\Services;

use App\Models\Clinic;
use Illuminate\Http\Request;

class TenantResolver
{
    public function resolve(Request $request): ?Clinic
    {
        $host = $request->getHost();

        $parts = explode('.', $host);

        if (count($parts) < 3) {
            return null;
        }

        $subdomain = $parts[0];

        return Clinic::on('central')
            ->withTrashed()
            ->where('slug', $subdomain)
            ->first();
    }
}
