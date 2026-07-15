<?php

use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\CronController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\ApiController;
use App\Http\Controllers\TempMailController;
use Illuminate\Support\Facades\Route;

Route::get('cron/{password}', [CronController::class, 'cron']);
Route::get('blogs', [BlogController::class, 'getBlogs']);

Route::prefix('delivery')->middleware('verify.delivery')->group(function () {
    Route::post('verify', [DeliveryController::class, 'verify']);
    Route::get('stats/{filters?}', [DeliveryController::class, 'stats']);
    Route::post('message/store/{key}', [DeliveryController::class, 'storeMessage']);
    Route::get('messages/{page}/{limit}/{search?}', [DeliveryController::class, 'messages']);
    Route::delete('message/{message_id}', [DeliveryController::class, 'deleteMessage']);
});

Route::get('domains/{key}', [ApiController::class, 'domains']);
Route::get('email/{email}/{key}', [ApiController::class, 'email']);
Route::get('messages/{email}/{key}', [ApiController::class, 'messages']);
Route::get('message/{message_id}/{key}', [ApiController::class, 'message']);
Route::delete('message/{message_id}/{key}', [ApiController::class, 'delete']);

// DB-backed Gmail dot temp mail system
Route::post('temp-mail/new', [TempMailController::class, 'create']);
Route::get('temp-mail/inbox/{id}', [TempMailController::class, 'inbox']);
Route::delete('temp-mail/{id}', [TempMailController::class, 'delete']);
