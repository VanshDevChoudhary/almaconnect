<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Routes status-gated users away from the app.
 *
 * Assumes `auth` and `verified` have already run, so the user exists and
 * has a verified email by the time this middleware is reached.
 */
class EnsureAlumniApproved
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        return match ($user->status) {
            'approved' => $next($request),
            'rejected' => redirect()->route('access-denied', ['reason' => 'rejected']),
            'banned' => redirect()->route('access-denied', ['reason' => 'banned']),
            default => redirect()->route('pending-review'),
        };
    }
}
