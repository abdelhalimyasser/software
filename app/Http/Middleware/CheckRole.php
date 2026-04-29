<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * @param Request $request
     * @param Closure $next
     * @param string ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $userRole = $user->role->value;

        if (!in_array($userRole, $roles)) {
            return response()->json([
                'error' => 'Forbidden - You do not have permission to access this resource.'
            ], 403);
        }

        return $next($request);
    }
}
