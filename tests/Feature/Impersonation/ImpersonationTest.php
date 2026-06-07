<?php

declare(strict_types=1);

use App\Models\ImpersonationLog;
use App\Models\User;
use App\Services\ImpersonationService;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

beforeEach(function (): void {
    $this->admin = User::factory()->create(['admin' => true]);
    $this->target = User::factory()->create();
});

function aktiveImpersonationSession(User $admin, ImpersonationLog $impersonationLog, ?Carbon $expiresAt = null): array
{
    return [
        ImpersonationService::SESSION_KEY => [
            'admin_id' => $admin->getKey(),
            'log_id' => $impersonationLog->getKey(),
            'expires_at' => ($expiresAt ?? Carbon::now()->addMinutes(ImpersonationService::MAX_DURATION_MINUTES))->toIso8601String(),
        ],
    ];
}

function impersonationLogFuer(User $admin, User $target): ImpersonationLog
{
    return ImpersonationLog::create([
        'admin_id' => $admin->getKey(),
        'target_id' => $target->getKey(),
        'started_at' => Carbon::now(),
    ]);
}

it('startet eine Impersonation als Admin und protokolliert sie', function (): void {
    $this->actingAs($this->admin);

    ImpersonationService::start($this->admin, $this->target);

    expect(Auth::id())->toBe($this->target->getKey())
        ->and(ImpersonationService::isImpersonating())->toBeTrue()
        ->and(ImpersonationService::originalAdmin()?->getKey())->toBe($this->admin->getKey());

    $impersonationLog = ImpersonationLog::sole();
    expect($impersonationLog->admin_id)->toBe($this->admin->getKey())
        ->and($impersonationLog->target_id)->toBe($this->target->getKey())
        ->and($impersonationLog->started_at)->not->toBeNull()
        ->and($impersonationLog->ended_at)->toBeNull();
});

it('rotiert die Session-ID beim Start gegen Session-Fixation', function (): void {
    $this->actingAs($this->admin);
    $oldSessionId = session()->getId();

    ImpersonationService::start($this->admin, $this->target);

    expect(session()->getId())->not->toBe($oldSessionId);
});

it('verweigert den Start für Non-Admins', function (): void {
    $otherUser = User::factory()->create();

    ImpersonationService::start($this->target, $otherUser);
})->throws(AuthorizationException::class, 'Nur Administratoren dürfen impersonieren.');

it('verweigert die Impersonation eines Admins', function (): void {
    $otherAdmin = User::factory()->create(['admin' => true]);

    ImpersonationService::start($this->admin, $otherAdmin);
})->throws(AuthorizationException::class, 'Administratoren können nicht impersoniert werden.');

it('verweigert die Impersonation eines gebannten Benutzers', function (): void {
    $this->target->ban();
    $this->target->refresh();

    ImpersonationService::start($this->admin, $this->target);
})->throws(AuthorizationException::class, 'Gebannte Benutzer können nicht impersoniert werden.');

it('verweigert Selbst-Impersonation', function (): void {
    ImpersonationService::start($this->admin, $this->admin);
})->throws(AuthorizationException::class);

it('verweigert verschachtelte Impersonation', function (): void {
    $this->actingAs($this->admin);
    ImpersonationService::start($this->admin, $this->target);

    ImpersonationService::start($this->admin, User::factory()->create());
})->throws(AuthorizationException::class, 'Es läuft bereits eine Impersonation.');

it('stellt den Admin bei stop wieder her und schließt das Protokoll', function (): void {
    $this->actingAs($this->admin);
    ImpersonationService::start($this->admin, $this->target);

    ImpersonationService::stop();

    expect(Auth::id())->toBe($this->admin->getKey())
        ->and(ImpersonationService::isImpersonating())->toBeFalse()
        ->and(ImpersonationLog::sole()->ended_at)->not->toBeNull();
});

it('erzwingt Logout bei stop wenn der Original-Admin kein Admin mehr ist', function (): void {
    $this->actingAs($this->admin);
    ImpersonationService::start($this->admin, $this->target);

    $this->admin->forceFill(['admin' => false])->save();
    ImpersonationService::stop();

    expect(Auth::guest())->toBeTrue()
        ->and(ImpersonationLog::sole()->ended_at)->not->toBeNull();
});

it('beendet eine abgelaufene Impersonation automatisch per Middleware', function (): void {
    $impersonationLog = impersonationLogFuer($this->admin, $this->target);

    $this->actingAs($this->target)
        ->withSession(aktiveImpersonationSession($this->admin, $impersonationLog, Carbon::now()->subMinute()))
        ->get('/pr0p0ll')
        ->assertRedirect('/pr0p0ll');

    $this->assertAuthenticatedAs($this->admin);
    expect($impersonationLog->fresh()->ended_at)->not->toBeNull();
});

it('bricht mit 403 ab wenn der impersonierte User Admin ist', function (): void {
    $adminTarget = User::factory()->create(['admin' => true]);
    $impersonationLog = impersonationLogFuer($this->admin, $adminTarget);

    $this->actingAs($adminTarget)
        ->withSession(aktiveImpersonationSession($this->admin, $impersonationLog))
        ->get('/pr0p0ll')
        ->assertForbidden();

    expect($impersonationLog->fresh()->ended_at)->not->toBeNull();
});

it('beendet die Impersonation wenn der Original-Admin gelöscht wurde', function (): void {
    $impersonationLog = impersonationLogFuer($this->admin, $this->target);
    $aSessionData = aktiveImpersonationSession($this->admin, $impersonationLog);
    $this->admin->delete();

    $this->actingAs($this->target)
        ->withSession($aSessionData)
        ->get('/pr0p0ll')
        ->assertRedirect('/');

    expect($impersonationLog->fresh()->ended_at)->not->toBeNull();
});

it('stellt den Admin über die Leave-Route wieder her', function (): void {
    $impersonationLog = impersonationLogFuer($this->admin, $this->target);

    $this->actingAs($this->target)
        ->withSession(aktiveImpersonationSession($this->admin, $impersonationLog))
        ->post(route('impersonation.leave'))
        ->assertRedirect('/pr0p0ll');

    $this->assertAuthenticatedAs($this->admin);
    expect($impersonationLog->fresh()->ended_at)->not->toBeNull();
});

it('lässt die Leave-Route ohne aktive Impersonation ins Leere laufen', function (): void {
    $this->actingAs($this->admin)
        ->post(route('impersonation.leave'))
        ->assertRedirect('/pr0p0ll');

    $this->assertAuthenticatedAs($this->admin);
});

it('verlangt Authentifizierung für die Leave-Route', function (): void {
    $this->post(route('impersonation.leave'))->assertRedirect();
});

it('räumt die Session auf wenn der impersonierte User extern ausgeloggt wurde', function (): void {
    $impersonationLog = impersonationLogFuer($this->admin, $this->target);

    $this->withSession(aktiveImpersonationSession($this->admin, $impersonationLog))
        ->get('/')
        ->assertRedirect('/pr0p0ll');

    $this->assertAuthenticatedAs($this->admin);
    expect($impersonationLog->fresh()->ended_at)->not->toBeNull();
});
