<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use Auth;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Exception;
use Filament\Actions\Action;
use Filament\Auth\Notifications\VerifyEmail;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;

class UserSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public ?array $data = [];

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog';

    protected static ?int $navigationSort = 100;

    protected static ?string $slug = 'benutzer-einstellungen';

    protected string $view = 'filament.pages.user-settings-page';

    protected static string|\UnitEnum|null $navigationGroup = 'Einstellungen';

    protected static ?string $title = 'Einstellungen';

    protected static ?string $discordSupportUrl = 'https://discord.com/channels/1201613873514549392/1201613874034655298';

    private ?User $currentUser;

    /**
     * @throws Halt
     */
    public function mount(): void
    {
        $this->currentUser = Auth::user();
        if (! $this->currentUser) {
            throw new Halt('User nicht gefunden');
        }
        $demoGraphicData = $this->currentUser->getDemographicData() ?? [];
        $notificationSettings = $this->currentUser->getNotificationSettingsForForm();

        $notificationSettings = [
            'notification_settings' => $notificationSettings,
        ];
        $data = array_merge($demoGraphicData, $notificationSettings);
        $data['name'] = $this->currentUser->name;
        $data['email'] = $this->currentUser->email;

        $this->form->fill($data);
    }

    public static function canAccess(): bool
    {
        return Auth::check();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('delete_account')->requiresConfirmation()->schema([
                TextEntry::make('info')->label('Information')->state('Bist du dir absolut sicher, dass du deinen Account löschen möchtest? Wir löschen alle Daten, die mit dir im Zusammenhang stehen. Diese Aktion kann nicht rückgängig gemacht werden!'),
            ])->action(function () {
                $user = Auth::user();

                if ($user) {
                    $user->delete();
                } else {
                    throw new Halt;
                }

            })->label('Account löschen')->color('danger'),
            Action::make('support')
                ->url(self::$discordSupportUrl)->openUrlInNewTab()->label('Ich brauche Hilfe!'),
        ];
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make(['sm' => 1, 'lg' => 2])->schema([
                Section::make('Demografische Daten')
                    ->description('Werden ausschließlich für die Zielgruppen-Auswahl von Umfragen verwendet.')
                    ->icon('heroicon-o-identification')
                    ->columnSpan(1)
                    ->schema([
                        Grid::make(['sm' => 1, 'md' => 2])->schema([
                            Select::make('gender')
                                ->label('Geschlecht')
                                ->options(Gender::class)
                                ->native(false)
                                ->disabled(fn (): bool => ! Auth::user()->canUpdateDemographicData()),
                            DatePicker::make('birthday')
                                ->label('Geburtstag')
                                ->nullable()
                                ->before('today')
                                ->displayFormat('d.m.Y')
                                ->disabled(fn (): bool => ! Auth::user()->canUpdateDemographicData()),
                            Select::make('nationality')
                                ->label('Nationalität')
                                ->searchable()
                                ->options(Nationality::class)
                                ->native(false)
                                ->disabled(fn (): bool => ! Auth::user()->canUpdateDemographicData()),
                            Select::make('region')
                                ->label('Region')
                                ->searchable()
                                ->options(Region::class)
                                ->native(false)
                                ->disabled(fn (): bool => ! Auth::user()->canUpdateDemographicData()),
                        ]),
                        Placeholder::make('info')
                            ->hiddenLabel()
                            ->content('Demografische Daten können nur alle 2 Monate geändert werden, um zu verhindern, dass sich Teilnehmer für Umfragen als andere Zielgruppe ausgeben.'),
                        Placeholder::make('next_change')
                            ->hiddenLabel()
                            ->content(function (): string {
                                $dLastChange = Carbon::make(Auth::user()->last_data_change);

                                if ($dLastChange === null || $dLastChange->addMonths(2)->isPast()) {
                                    return 'Nächste Änderung möglich: sofort';
                                }

                                $dNextChange = $dLastChange->addMonths(2);

                                return sprintf(
                                    'Nächste Änderung möglich in %d Tagen (%s Uhr)',
                                    $dNextChange->diffInDays(),
                                    $dNextChange->format('d.m.Y H:i'),
                                );
                            }),
                    ]),

                Section::make('Benutzerdaten')
                    ->description('Anzeige­name und Kontakt für Benachrichtigungen.')
                    ->icon('heroicon-o-user-circle')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('name')
                            ->label('Benutzername')
                            ->disabled(),
                        TextInput::make('email')
                            ->label('E-Mail')
                            ->helperText('Wird ausschließlich für Benachrichtigungen verwendet.')
                            ->unique(table: 'users', column: 'email', ignorable: Auth::user())
                            ->nullable()
                            ->email()
                            ->suffixIcon(fn (): ?string => Auth::user()->hasVerifiedEmail() ? 'heroicon-o-check-badge' : null)
                            ->suffixIconColor(fn (): string => Auth::user()->hasVerifiedEmail() ? 'success' : 'warning'),
                        Actions::make([
                            Action::make('resend_email_verification')
                                ->icon('heroicon-o-check-badge')
                                ->color('warning')
                                ->label('E-Mail-Verifizierung erneut senden')
                                ->action(fn () => $this->resendEmailVerificationEmail()),
                        ])
                            ->fullWidth()
                            ->visible(fn (): bool => Filament::auth()->user()?->email !== null && Filament::auth()->user()?->hasVerifiedEmail() === false),
                    ]),
            ]),

            Section::make('Benachrichtigungs-Einstellungen')
                ->description('Wähle pro Kanal, welche Ereignisse dich erreichen sollen.')
                ->icon('heroicon-o-bell')
                ->collapsible()
                ->schema([
                    Tabs::make('notification_channels')
                        ->tabs(function (): array {
                            return NotificationChannel::all()->map(function (NotificationChannel $notificationChannel): Tab {
                                return Tab::make($notificationChannel->title)
                                    ->label($notificationChannel->title)
                                    ->icon($notificationChannel->icon)
                                    ->schema(function () use ($notificationChannel): array {
                                        return NotificationType::all()->map(function (NotificationType $notificationType) use ($notificationChannel): Toggle {
                                            return Toggle::make('notification_settings.'.$notificationChannel->getKey().'.'.$notificationType->getKey())
                                                ->label($notificationType->title)
                                                ->helperText($notificationType->description);
                                        })->all();
                                    });
                            })->all();
                        }),
                ]),
        ])->statePath('data');
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
        ];
    }

    public function save(): void
    {
        try {
            $user = Auth::user();
            if ($user === null) {
                throw new Halt('User nicht gefunden');
            }

            $data = $this->form->getState();
            $aOriginalDemographicData = $user->getDemographicData();
            ksort($aOriginalDemographicData);
            $demoGraphicDataKeys = array_keys($aOriginalDemographicData);
            $demographicData = array_filter($data, static fn ($key) => in_array($key, $demoGraphicDataKeys, true), ARRAY_FILTER_USE_KEY);
            ksort($demographicData);

            if ($user->last_data_change === null || Carbon::make($user->last_data_change)->addMonths(2)->isPast()) {
                // Check if dirty ignore array order and compare
                if ($aOriginalDemographicData !== $demographicData) {
                    $user->update($demographicData);
                    $user->update([
                        'last_data_change' => Carbon::now(),
                    ]);
                }
            }
            if ($user->email !== $data['email']) {
                $user->update([
                    'email_verified_at' => null,
                ]);
                $user->update([
                    'email' => $data['email'],
                ]);
                if ($user->email !== null) {
                    $notification = new VerifyEmail;
                    $notification->url = Filament::getVerifyEmailUrl($user);

                    $user->notify($notification);
                    Notification::make('email_verification_needed')->warning()->title('E-Mail bestätigen')->body('Bevor Benachrichtigungen an diese Adresse gesendet werden musst du deine E-Mail bestätigen.')->send();
                }
            }

            // Update notification settings
            $user->updateNotificationSettings($data['notification_settings']);
            Notification::make('saved_successfully')->success()->title('Erfolgreich gespeichert')->send();
        } catch (Halt $exception) {
            return;
        }
    }

    /**
     * @throws Exception
     */
    protected function resendEmailVerificationEmail(): void
    {
        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            Notification::make()
                ->title(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled.title', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]))
                ->body(array_key_exists('body', __('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled') ?: []) ? __('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resend_throttled.body', [
                    'seconds' => $exception->secondsUntilAvailable,
                    'minutes' => ceil($exception->secondsUntilAvailable / 60),
                ]) : null)
                ->danger()
                ->send();

            return;
        }

        $user = Filament::auth()->user();

        if (! method_exists($user, 'notify')) {
            $userClass = $user::class;

            throw new Exception("Model [{$userClass}] does not have a [notify()] method.");
        }

        $notification = new VerifyEmail;
        $notification->url = Filament::getVerifyEmailUrl($user);

        $user->notify($notification);

        Notification::make()
            ->title(__('filament-panels::pages/auth/email-verification/email-verification-prompt.notifications.notification_resent.title'))
            ->success()
            ->send();
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('save')
            ->label(__('filament-panels::resources/pages/edit-record.form.actions.save.label'))
            ->requiresConfirmation()
            ->submit('save')
            ->keyBindings(['mod+s']);
    }
}
