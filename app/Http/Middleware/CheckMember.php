<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMember {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if (config('app.settings.user_registration.enable', false) && config('app.settings.user_registration.require_email_verification', false) && Auth::check()) {
            $user = Auth::user();
            if (!$user->hasVerifiedEmail() && $user->role != 7) {
                return redirect('/email/verify');
            }
        }
        return $next($request);
    }
}
