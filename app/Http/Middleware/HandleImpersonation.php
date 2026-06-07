<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ImpersonationService;
use Closure;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleImpersonation
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! ImpersonationService::isImpersonating()) {
            return $next($request);
        }

        if (Auth::guest()) {
            // Der impersonierte User wurde extern ausgeloggt (z.B. durch einen Ban) — aufräumen.
            ImpersonationService::stop();

            return redirect()->to('/pr0p0ll');
        }

        if (ImpersonationService::isExpired()) {
            ImpersonationService::stop();

            Notification::make()
                ->title('Impersonation abgelaufen')
                ->body('Die Impersonation wurde nach '.ImpersonationService::MAX_DURATION_MINUTES.' Minuten automatisch beendet.')
                ->warning()
                ->send();

            return redirect()->to('/pr0p0ll');
        }

        $admin = ImpersonationService::originalAdmin();

        if ($admin === null || ! $admin->isAdmin()) {
            // Original-Admin existiert nicht mehr oder hat seine Rechte verloren — Session beenden.
            ImpersonationService::stop();

            return redirect()->to('/');
        }

        if (Auth::user()->isAdmin()) {
            // Defense-in-depth: ein impersonierter User darf nie Admin sein.
            ImpersonationService::stop();

            abort(403, 'Impersonation eines Administrators ist nicht erlaubt.');
        }

        return $next($request);
    }
}
