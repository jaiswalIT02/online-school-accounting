<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleAccountSelection
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     return $next($request);
    // }

    public function handle($request, Closure $next)
    {
        // dd('hie');
        if ($request->filled('account_type')) {
            session(['account_type' => $request->account_type]);
        }

        if ($request->filled('session_id')) {
            session(['session_id' => $request->session_id]);
        }

        return $next($request);
    }
}
