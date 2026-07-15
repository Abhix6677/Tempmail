<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDelivery {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        $token = $request->route('key');
        if ($token == null) {
            $token = $request->bearerToken();
        }
        if ($token == config('app.settings.delivery.key')) {
            return $next($request);
        }
        return response()->json([
            'error' => 'Invalid Key'
        ], 401);
    }
}
