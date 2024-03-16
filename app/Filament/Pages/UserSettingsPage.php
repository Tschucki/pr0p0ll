<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Actions\Action as FormAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Auth\VerifyEmail;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Carbon;

class UserSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;
    use WithRateLimiting;

    public ?array $data = [];

    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static ?int $navigationSort = 100;

    protected static ?string $slug = 'benutzer-einstellungen';

    protected static string $view = 'filament.pages.user-settings-page';

    protected static ?string $navigationGroup = 'Einstellungen';

    protected static ?string $title = 'Einstellungen';

    protected static ?string $discordSupportUrl = 'https://discord.com/channels/1201613873514549392/1201613874034655298';

    private ?User $currentUser;

    /**
     * @throws Halt
     */
    public function mount(): void
    {
        $this->currentUser = \Auth::user();
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
        return \Auth::check();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('delete_account')->requiresConfirmation()->infolist([
                TextEntry::make('info')->label('Information')->state('Bist du dir absolut sicher, dass du deinen Account löschen möchtest? Wir löschen alle Daten, die mit dir im Zusammenhang stehen. Diese Aktion kann nicht rückgängig gemacht werden!'),
            ])->action(function () {
                $user = \Auth::user();

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

    public function form(Form $form): Form
    {
        return $form->schema([
            Grid::make(2)->schema([
                Section::make('Demografische Daten')->schema([

                    Select::make('gender')->disabled(fn () => ! \Auth::user()->canUpdateDemographicData())->label('Geschlecht')->options(Gender::class),
                    DatePicker::make('birthday')->disabled(fn () => ! \Auth::user()->canUpdateDemographicData())
                        ->label('Geburtstag')
                        ->nullable()
                        ->before('today')
                        ->displayFormat('d.m.Y'),
                    Select::make('nationality')->disabled(fn () => ! \Auth::user()->canUpdateDemographicData())->searchable()->label('Nationalität')->options(Nationality::class),
                    Select::make('region')->disabled(fn () => ! \Auth::user()->canUpdateDemographicData())->searchable()->label('Region')->options(Region::class),
                    Placeholder::make('info')->label('')->content('Demografische Daten können nur alle 2 Monate geändert werden um zu verhindern, dass man sich für Umfragen als andere Zielgruppe ausgibt.')->extraAttributes(['class' => 'text-justify']),
                    Placeholder::make('next_change')->label('')->content(function () {
                        $sText = 'Deine nächste Änderung ist möglich in: ';
                        $dLastChange = Carbon::make(\Auth::user()->last_data_change);

                        if ($dLastChange === null) {
                            $sText .= 'Sofort';

                            return $sText;
                        }

                        $dNextChange = $dLastChange->addMonths(2);

                        if ($dNextChange->isPast()) {
                            $sText .= 'Sofort';

                            return $sText;
                        }

                        $sText .= $dNextChange->diffInDays().' Tagen'." ({$dNextChange->format('d.m.Y H:i')} Uhr)";

                        return $sText;
                    })->extraAttributes(['class' => 'text-justify']),
                ])->columnSpan(1),
                Section::make('Benutzerdaten')->schema([
                    TextInput::make('name')->label('Benutzername')->disabled(),
                    TextInput::make('email')->label('E-Mail')->helperText('Für Benachrichtigungen')->unique(table: 'users', column: 'email', ignorable: \Auth::user())->nullable()->email()->suffixIcon(fn () => \Auth::user()->hasVerifiedEmail() ? 'heroicon-o-check-badge' : '')->suffixIconColor(fn () => \Auth::user()->hasVerifiedEmail() ? 'success' : 'warning'),
                    Actions::make([
                        FormAction::make('resend_email_verification')->icon('heroicon-o-check-badge')->color('warning')->label('E-Mail-Verifizierung erneut senden')->action(fn () => $this->resendEmailVerificationEmail()),
                    ])->fullWidth()->visible(fn () => Filament::auth()->user()?->email !== null && Filament::auth()->user()?->hasVerifiedEmail() === false),
                ])->extraAttributes([
                    'class' => 'h-full',
                ])->columnSpan(1),
            ]),
            Section::make('Benachrichtigungs-Einstellungen')->schema([
                Tabs::make('Test')->tabs(function () {
                    $tabs = [];
                    NotificationChannel::each(function (NotificationChannel $notificationChannel) use (&$tabs) {
                        $tabs[] = Tabs\Tab::make($notificationChannel->title)->label($notificationChannel->title)->icon($notificationChannel->icon)->schema(function () use ($notificationChannel) {
                            $items = [];
                            NotificationType::each(function (NotificationType $notificationType) use (&$items, $notificationChannel) {
                                $items[] = Toggle::make('notification_settings.'.$notificationChannel->getKey().'.'.$notificationType->getKey())->label($notificationType->title)->helperText($notificationType->description);
                            });

                            return $items;
                        });
                    });

                    return $tabs;
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
            $user = \Auth::user();
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
