<?php

use App\Http\Controllers\Pr0authController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // TODO: Add Landing
    return Redirect::route('filament.pr0p0ll.pages.dashboard');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');

    Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');

    Route::get('login', function () {
        return Redirect::route('filament.pr0p0ll.auth.login');
    })->name('login');
});
