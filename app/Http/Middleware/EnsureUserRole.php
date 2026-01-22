<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('user.login');
        }

        // User portal hanya untuk role 'user'
        if (!$user->hasRole('user')) {
            // Kalau admin nyasar ke portal user, lempar ke admin dashboard
            if ($user->hasAnyRole(['super_admin', 'petugas'])) {
                return redirect()->route('admin.dashboard');
            }

            abort(403, 'Akses user ditolak.');
        }

        return $next($request);
    }
}
