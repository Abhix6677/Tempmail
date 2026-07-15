<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Artisan;

class VerifyInstall {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if (!file_exists(storage_path('installed')) && $request->route()->getName() !== 'installer') {
            Artisan::call('key:generate', ["--force" => true]);
            return redirect()->route('installer');
        }
        return $next($request);
    }
}
