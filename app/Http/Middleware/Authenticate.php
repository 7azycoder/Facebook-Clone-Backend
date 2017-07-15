<?php

namespace App\Http\Middleware;

use Closure;
use App\User;

class Authenticate
{
    /**
     * Run the request filter.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->cookie('access_token')) {
            $user = User::where('token', $request->cookie('access_token'))->first();
            if($user){
                return $next($user);
            } else{
                return response('Authentication Error', 401);
            }
        }

        return response('Authentication Error', 401);
    }

}
