<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class Role {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $role): Response {
        $roles = [
            'admin' => 7
        ];
        $user = Auth::user();
        if ($user) {
            if ($user->role == $roles[$role]) {
                app()->setLocale('en');
                return $next($request);
            }
        }
        return abort(403);
    }
}
