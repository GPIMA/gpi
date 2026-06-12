<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to one or more roles, e.g. ->middleware('role:ADMIN')
 * or ->middleware('role:ADMIN,TECHNICIEN').
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role->value, $roles, true)) {
            abort(403, __('messages.forbidden'));
        }

        return $next($request);
    }
}
