<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Auth;
use Filament\Schemas\Schema;
use Redirect;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;

class Login extends \Filament\Auth\Pages\Login
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.login';

    protected function getAuthenticateFormAction(): Action
    {
        return Action::make('authenticate')
            ->extraAttributes(['id' => 'loginButton'])
            ->label('Mit pr0gramm anmelden')
            ->submit('login');
    }

    protected function getBackAction()
    {
        return Action::make('back')->link()->label('Zurück zur Startseite')->url(route('frontend.landing'));
    }

    public function mount(): void
    {
        if (Filament::auth()->check()) {
            redirect()->intended(Filament::getUrl());
        }
    }

    public static function canAccess(): bool
    {
        return Auth::check() === false;
    }

    public function getForm(string $name): ?Schema
    {
        return null;
    }

    public function getHeading(): string|Htmlable
    {
        return 'Mit pr0gramm anmelden';
    }

    public function login()
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/login.notifications.throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/login.notifications.throttled') ?: []) ? __('filament-panels::pages/auth/login.notifications.throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }

        return Redirect::route('oauth.start');
    }
}
