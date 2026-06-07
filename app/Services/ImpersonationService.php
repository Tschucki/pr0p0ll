<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ImpersonationLog;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ImpersonationService
{
    public const SESSION_KEY = 'impersonation';

    public const MAX_DURATION_MINUTES = 30;

    /**
     * Startet eine Impersonation. Einziger erlaubter Einstiegspunkt —
     * alle Guards laufen serverseitig, die Admin-ID kommt nie aus Request-Input.
     *
     * @throws AuthorizationException
     */
    public static function start(User $admin, User $target): void
    {
        if (! $admin->isAdmin()) {
            throw new AuthorizationException('Nur Administratoren dürfen impersonieren.');
        }

        if ($target->isAdmin()) {
            throw new AuthorizationException('Administratoren können nicht impersoniert werden.');
        }

        if ($target->isBanned()) {
            throw new AuthorizationException('Gebannte Benutzer können nicht impersoniert werden.');
        }

        if ($target->is($admin)) {
            throw new AuthorizationException('Du kannst dich nicht selbst impersonieren.');
        }

        if (self::isImpersonating()) {
            throw new AuthorizationException('Es läuft bereits eine Impersonation.');
        }

        $impersonationLog = ImpersonationLog::create([
            'admin_id' => $admin->getKey(),
            'target_id' => $target->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'started_at' => Carbon::now(),
        ]);

        session()->put(self::SESSION_KEY, [
            'admin_id' => $admin->getKey(),
            'log_id' => $impersonationLog->getKey(),
            'expires_at' => Carbon::now()->addMinutes(self::MAX_DURATION_MINUTES)->toIso8601String(),
        ]);

        // Auth::login() rotiert die Session-ID (Schutz vor Session-Fixation) und behält die Session-Daten.
        Auth::login($target);
        self::storePasswordHashInSession($target);

        Log::info('Impersonation gestartet', [
            'admin_id' => $admin->getKey(),
            'target_id' => $target->getKey(),
            'log_id' => $impersonationLog->getKey(),
        ]);
    }

    /**
     * Beendet die Impersonation und stellt den Original-Admin wieder her.
     * Existiert der Admin nicht mehr oder hat er seine Admin-Rechte verloren,
     * wird stattdessen ein vollständiger Logout erzwungen.
     */
    public static function stop(): void
    {
        $aImpersonationData = session()->pull(self::SESSION_KEY);

        if (! is_array($aImpersonationData)) {
            return;
        }

        if (isset($aImpersonationData['log_id'])) {
            ImpersonationLog::whereKey($aImpersonationData['log_id'])
                ->whereNull('ended_at')
                ->update(['ended_at' => Carbon::now()]);
        }

        $admin = User::find($aImpersonationData['admin_id'] ?? null);

        if ($admin === null || ! $admin->isAdmin()) {
            Auth::logout();
            session()->invalidate();
            session()->regenerateToken();

            Log::warning('Impersonation beendet — Original-Admin ungültig, Logout erzwungen', [
                'admin_id' => $aImpersonationData['admin_id'] ?? null,
                'log_id' => $aImpersonationData['log_id'] ?? null,
            ]);

            return;
        }

        Auth::login($admin);
        self::storePasswordHashInSession($admin);

        Log::info('Impersonation beendet', [
            'admin_id' => $admin->getKey(),
            'log_id' => $aImpersonationData['log_id'] ?? null,
        ]);
    }

    public static function isImpersonating(): bool
    {
        return session()->has(self::SESSION_KEY);
    }

    public static function isExpired(): bool
    {
        $aImpersonationData = session()->get(self::SESSION_KEY);

        if (! is_array($aImpersonationData) || ! isset($aImpersonationData['expires_at'])) {
            return true;
        }

        return Carbon::parse($aImpersonationData['expires_at'])->isPast();
    }

    public static function originalAdmin(): ?User
    {
        $aImpersonationData = session()->get(self::SESSION_KEY);

        if (! is_array($aImpersonationData)) {
            return null;
        }

        return User::find($aImpersonationData['admin_id'] ?? null);
    }

    /**
     * AuthenticateSession vergleicht den Session-Hash mit dem Passwort des aktuellen Users
     * und würde nach dem User-Wechsel sonst sofort ausloggen.
     */
    private static function storePasswordHashInSession(User $user): void
    {
        session()->put('password_hash_'.Auth::getDefaultDriver(), $user->getAuthPassword());
    }
}
