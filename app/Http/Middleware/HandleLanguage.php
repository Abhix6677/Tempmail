<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class HandleLanguage {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        try {
            if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
                $locale = explode('-', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE'])[0])[0];
                if (isset(config('app.settings.languages')[$locale]) && config('app.settings.languages')[$locale]['is_active']) {
                    session(['browser-locale' => $locale]);
                }
            }
        } catch (\Exception $e) {
            Log::alert($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        $locale = session('locale', session('browser-locale', config('app.settings.language', config('app.locale', 'en'))));
        $rtl = false;
        if (isset(config('app.settings.languages')[$locale]) && config('app.settings.languages')[$locale]['type'] === 'rtl') {
            $rtl = true;
        }
        config(['app.settings.direction' => $rtl ? 'rtl' : 'ltr']);
        app()->setLocale($locale);
        return $next($request);
    }
}
