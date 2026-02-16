<?php

namespace Emir\Webartisan\Middleware;

use Closure;
use Emir\Webartisan\Webartisan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class WebartisanEnabled
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Webartisan::isEnabled()) {
            abort(404);
        }

        if (! Webartisan::check($request)) {
            abort(403, 'Webartisan is not available in this environment.');
        }

        $gate = config('webartisan.gate');

        if ($gate !== null && Gate::has($gate)) {
            if (! Gate::check($gate)) {
                abort(403, 'You are not authorized to access Webartisan.');
            }
        }

        return $next($request);
    }
}
