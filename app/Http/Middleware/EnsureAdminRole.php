<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('admin.login');
        }

        if (!$user->hasAnyRole(['super_admin', 'petugas'])) {
            abort(403, 'Akses admin ditolak.');
        }

        return $next($request);
    }
}
