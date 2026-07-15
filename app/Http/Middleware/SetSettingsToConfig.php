<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;
use Illuminate\Support\Facades\Log;

class SetSettingsToConfig {
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response {
        try {
            $options = Schema::hasTable((new Setting)->getTable()) ? Setting::get() : [];
            foreach ($options as $option) {
                config([
                    'app.settings.' . $option->key => unserialize($option->value)
                ]);
            }
            // Get theme from query parameter first, then from session
            $theme = $request->query('theme') ?: session('theme');

            // If we have a theme and it exists, store it in session and config
            if ($theme && is_dir(resource_path('views/frontend/themes/' . $theme))) {
                session(['theme' => $theme]);
                config(['app.settings.theme' => $theme]);
            }
        } catch (Exception  $e) {
            Log::alert($e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
        return $next($request);
    }
}
