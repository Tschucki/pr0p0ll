<?php

use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\LoginRedirectController;
use App\Http\Controllers\Pr0authController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'landing'])->name('frontend.landing');
Route::get('/impressum', [FrontendController::class, 'imprint'])->name('frontend.imprint');
Route::get('/datenschutz', [FrontendController::class, 'privacy'])->name('frontend.privacy');
Route::get('/nutzungsbedingungen', [FrontendController::class, 'terms'])->name('frontend.terms');

Route::middleware(['guest'])->group(function () {
    Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');

    Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');

    Route::get('login', LoginRedirectController::class)->name('login');
});
