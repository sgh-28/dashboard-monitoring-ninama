<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user()->loadMissing('role');
        $userRole = $user->role?->name ?? 'NULL';
        
        Log::info("ROLE CHECK: User={$user->email}, Role={$userRole}, Required=" . implode(',', $roles));
        
        if (!in_array($userRole, $roles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin.');
        }
        
        return $next($request);
    }
}