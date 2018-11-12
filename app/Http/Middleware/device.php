<?php

namespace App\Http\Middleware;

use Closure;

class device
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if($request->user() && $request->user()->role->id !== 3 && $request->user()->status !== 1) {
            return response()->json('Unauthorised Access',401);
        }
        return $next($request);
    }
}
