<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    // 'admin' role = super admin. 'controller' role (sub-admin) ei route gulo e dhukte parbe na
    public function handle(Request $request, Closure $next)
    {
        if (!$request->user() || !$request->user()->isSuperAdmin()) {
            return response()->json([
                'status'  => 403,
                'message' => 'Only the super admin can perform this action.',
            ], 403);
        }

        return $next($request);
    }
}
