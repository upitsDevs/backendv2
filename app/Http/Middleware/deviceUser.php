<?php

namespace App\Http\Middleware;

use Closure;
use App\User;
use Auth;
use App\device;

class deviceUser
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
        $device = device::where('deviceID','=',$request->input('device_id'))->firstOrFail();
        if( $device->user_id == $request->user()->id || $request->user()->role->id == 1){
            return $next($request);
        }
        return response()->json(['Message' => 'Not Allowed To Control This Device'],401);
        
    }
}
