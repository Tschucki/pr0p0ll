<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class LoginRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return Redirect::route('filament.pr0p0ll.auth.login');
    }
}
