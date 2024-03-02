<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use App\Models\NotificationChannel;
use App\Models\NotificationType;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Exceptions\Halt;

class UserSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

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
        if (!$this->currentUser) {
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
                    Select::make('gender')->label('Geschlecht')->options(Gender::class),
                    DatePicker::make('birthday')
                        ->label('Geburtstag')
                        ->nullable()
                        ->before('today')
                        ->displayFormat('d.m.Y'),
                    Select::make('nationality')->label('Nationalität')->options(Nationality::class),
                    Select::make('region')->label('Region')->options(Region::class),
                ])->columnSpan(1),
                Section::make('Benutzerdaten')->schema([
                    TextInput::make('name')->label('Benutzername')->disabled(),
                    TextInput::make('email')->label('E-Mail')->helperText('Für Benachrichtigungen')->nullable()->email()
                ])->extraAttributes([
                    'class' => 'h-full'
                ])->columnSpan(1),
            ]),
            Section::make('Benachrichtigungs-Einstellungen')->schema([
                Tabs::make('Test')->tabs(function () {
                    $tabs = [];
                    NotificationChannel::each(function (NotificationChannel $notificationChannel) use (&$tabs) {
                        $tabs[] = Tabs\Tab::make($notificationChannel->title)->label($notificationChannel->title)->icon($notificationChannel->icon)->schema(function () use ($notificationChannel) {
                            $items = [];
                            NotificationType::each(function (NotificationType $notificationType) use (&$items, $notificationChannel) {
                                $items[] = Toggle::make('notification_settings.' . $notificationChannel->getKey() . '.' . $notificationType->getKey())->label($notificationType->title)->helperText($notificationType->description);
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
            $demoGraphicDataKeys = array_keys($user->getDemographicData());
            $demographicData = array_filter($data, static fn($key) => in_array($key, $demoGraphicDataKeys, true), ARRAY_FILTER_USE_KEY);

            // Update demographic data
            $user->update($demographicData);

            if ($user->where('email')->doesntExist() && $user->where('email', $data['email'])->first()?->getKey() !== \Auth::user()->getKey()) {
                $user->update([
                    'email' => $data['email']
                ]);
            } else if ($user->where('email', $data['email'])->first()->getKey() !== \Auth::user()->getKey()) {
                Notification::make('email_not_unique')->danger()->title('Die E-mail konnte nicht gespeichert werden')->send();
            }

            // Update notification settings
            $user->updateNotificationSettings($data['notification_settings']);
            Notification::make('saved_successfully')->success()->title('Erfolgreich gespeichert')->send();
        } catch (Halt $exception) {
            return;
        }
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
