<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\ImpersonationService;
use Illuminate\Http\RedirectResponse;

class ImpersonationController extends Controller
{
    public function leave(): RedirectResponse
    {
        if (ImpersonationService::isImpersonating()) {
            ImpersonationService::stop();
        }

        return redirect()->to('/pr0p0ll');
    }
}
