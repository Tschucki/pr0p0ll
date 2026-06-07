<?php

declare(strict_types=1);

use App\Http\Controllers\Frontend\FrontendController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\LoginRedirectController;
use App\Http\Controllers\PollResultImageController;
use App\Http\Controllers\PollResultRenderController;
use App\Http\Controllers\Pr0authController;
use Illuminate\Support\Facades\Route;

Route::get('/', [FrontendController::class, 'landing'])->name('frontend.landing');
Route::get('/impressum', [FrontendController::class, 'imprint'])->name('frontend.imprint');
Route::get('/datenschutz', [FrontendController::class, 'privacy'])->name('frontend.privacy');
Route::get('/nutzungsbedingungen', [FrontendController::class, 'terms'])->name('frontend.terms');

// Filament-freie Auswertungs-Render-Seite (signiert), künftiges Bot-Screenshot-Ziel.
Route::get('/umfragen/{poll}/auswertung', PollResultRenderController::class)
    ->name('poll.results.render')
    ->middleware('signed');

// Download des asynchron erzeugten Auswertungs-Screenshots (verlinkt aus der Notification).
Route::get('/umfragen/{poll}/auswertung-bild', PollResultImageController::class)
    ->name('poll.results.image')
    ->middleware('auth');

// Beendet eine aktive Admin-Impersonation (POST + CSRF, kein GET — verhindert erzwungene Beendigung per Link).
Route::post('/impersonation/beenden', [ImpersonationController::class, 'leave'])
    ->name('impersonation.leave')
    ->middleware('auth');

Route::middleware(['guest'])->group(function () {
    Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');

    Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');

    Route::get('login', LoginRedirectController::class)->name('login');
});
