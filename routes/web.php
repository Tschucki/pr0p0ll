<?php

use App\Http\Controllers\Pr0authController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // TODO: Add Landing
    return Redirect::route('filament.pr0p0ll.pages.dashboard');
});

Route::get('/oauth/callback', [Pr0authController::class, 'callback'])->name('oauth.callback');

Route::get('/oauth/start', [Pr0authController::class, 'start'])->name('oauth.start');

Route::get('login', function () {
    return Redirect::route('filament.pr0p0ll.auth.login');
})->name('login');
