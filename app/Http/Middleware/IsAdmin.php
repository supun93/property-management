<?php
namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $redirectToRoute = null)
    {
        if (auth()->check() && auth()->user()->role == 1) {
            return $next($request);
        }
        abort(403, 'Unauthorized');
    }
}