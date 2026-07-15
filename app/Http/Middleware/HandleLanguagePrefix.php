<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleLanguagePrefix {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        if (config('app.settings.language_in_url') === true) {
            $currentLocale = app()->getLocale();
            $firstSegment = $request->segment(1);
            if (preg_match('/^[a-z]{2}$/i', $firstSegment)) {
                if (config('app.settings.languages')[$firstSegment] && config('app.settings.languages')[$firstSegment]['is_active']) {
                    $locale = $firstSegment;
                    app()->setLocale($locale);
                    session(['locale' => $locale]);
                } else {
                    return redirect()->to('/' . $currentLocale . $request->getRequestUri());
                }
            } else {
                return redirect()->to('/' . $currentLocale . $request->getRequestUri());
            }
        }
        return $next($request);
    }
}
