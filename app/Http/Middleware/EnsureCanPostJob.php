<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCanPostJob
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, ['alumni', 'admin'], true)) {
            abort(403, 'Only alumni can post jobs.');
        }

        return $next($request);
    }
}
