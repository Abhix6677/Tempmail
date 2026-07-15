<?php

use App\Http\Controllers\AppController;
use App\Http\Controllers\UploadController;
use App\Http\Controllers\WidgetController;
use App\Models\Setting;
use App\Models\User;
use App\Services\Util;
use Illuminate\Support\Facades\Route;

Route::get('/installer', function () {
    if (file_exists(storage_path('installed'))) {
        return redirect(Util::localizeRoute('home'));
    } else {
        return view('installer.index');
    }
})->name('installer');

Route::middleware(['verify.install', 'handle.language'])->group(function () {
    /** Admin Routes */
    Route::middleware(['auth:sanctum', config('jetstream.auth_session')])->group(function () {
        Route::prefix('admin')->middleware(['role:admin'])->group(function () {
            Route::get('/', function () {
                return redirect()->route('dashboard');
            })->name('admin');
            Route::get('/dashboard', function () {
                return view('backend.dashboard');
            })->name('dashboard');
            Route::get('/menu', function () {
                return view('backend.menu');
            })->name('menu');
            Route::get('/pages', function () {
                return view('backend.pages');
            })->name('pages');
            Route::get('/blog', function () {
                return view('backend.blog');
            })->name('blog');
            Route::get('/domains', function () {
                return view('backend.domains');
            })->name('domains');
            Route::get('/settings', function () {
                return view('backend.settings');
            })->name('settings');
            Route::get('/users', function () {
                return view('backend.users');
            })->name('users');
            Route::get('/themes', function () {
                return view('backend.themes');
            })->name('themes');
            Route::get('/updates', function () {
                return view('backend.updates');
            })->name('updates');
            /** Tinymce Image Upload Route */
            Route::post('/upload/tinymce/image', [UploadController::class, 'tinymceImage']);
        });
    });
    /** Auth Routes */
    if (Util::checkDatabaseConnection()) {
        $hasAdmin = User::where('role', 7)->exists();
        $user_registration = Setting::pick('user_registration');
        // Block registration only if: setting exists, enabled is false, AND at least one admin exists
        // This allows first-time registration when no admin has been created yet
        if ($user_registration && $user_registration['enabled'] === false && $hasAdmin) {
            Route::get('register', function () {
                return abort(404);
            })->name('register');
            Route::post('register', function () {
                return abort(404);
            });
        }
    }
    Route::post('unlock', [AppController::class, 'unlock'])->name('unlock');
    Route::post('locale', [AppController::class, 'locale'])->name('locale');
    Route::post('widget/contact', [WidgetController::class, 'contact'])->name('widget.contact');
    Route::get('sitemap.xml', [AppController::class, 'sitemap']);
    Route::middleware(['app.lock', 'check.member', 'handle.language.prefix'])->group(function () {
        $frontendRoutes = function () {
            /** Frontend Routes */
            Route::get('/', [AppController::class, 'load'])->name('home');
            Route::get('mailbox/{email?}', [AppController::class, 'mailbox'])->name('mailbox');
            Route::get('message/{messageId}', [AppController::class, 'message'])->name('message');
            Route::get('switch/{email}', [AppController::class, 'switch'])->name('switch');
            Route::get('profile', [AppController::class, 'profile'])->name('profile');
            Route::get('blog/{slug}', [AppController::class, 'blog'])->name('blog.post');
            Route::get('category/{slug}', [AppController::class, 'category'])->name('blog.category');
            Route::get('{slug}', [AppController::class, 'page'])->name('page');
        };
        if (Util::checkDatabaseConnection()) {
            $language_in_url = Setting::pick('language_in_url');
            if ($language_in_url) {
                $languages = Setting::pick('languages');
                foreach ($languages as $code => $language) {
                    if ($language['is_active'] === true) {
                        Route::prefix($code)->group($frontendRoutes);
                    }
                }
            }
        }
        $frontendRoutes();
    });
});
