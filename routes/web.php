<?php

use App\Http\Controllers\Pr0authController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', [\App\Http\Controllers\Frontend\FrontendController::class, 'landing'])->name('frontend.landing');

Route::middleware(['guest'])->group(function () {
    Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');

    Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');

    Route::get('login', function () {
        Auth::login(User::find(1));
        return Redirect::route('frontend.landing');
        return Redirect::route('filament.pr0p0ll.auth.login');
    })->name('login');
});
