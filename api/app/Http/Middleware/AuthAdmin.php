<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $roles = array('admin');
        if (!Auth::check()) {
            return response([
                'errors' => ['Invalid credentials']
            ], 403);
        } else {
            if (in_array(Auth::user()->role->type, $roles)) {
                return $next($request);
            } else {
                return response([
                    'errors' => ['You don\'t have permissions to do this']
                ], 403);
            }
        }
    }
}
