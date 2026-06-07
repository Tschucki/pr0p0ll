<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Filament\Resources\UserResource\Pages\ViewUser;
use App\Models\User;
use App\Services\ImpersonationService;
use Auth;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Component;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $slug = 'benutzer';

    protected static ?string $label = 'Benutzer';

    protected static ?string $pluralLabel = 'Benutzer';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';

    public static function canAccess(): bool
    {
        return Auth::user()->isAdmin();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
                TextColumn::make('name')
                    ->label('Name')
                    ->icon('heroicon-o-user')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('polls_count')
                    ->label('Umfragen')
                    ->counts('polls')
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('participations_count')
                    ->label('Teilnahmen')
                    ->counts('participations')
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('email_verified_at')
                    ->label('E-Mail verifiziert')
                    ->boolean()
                    ->sortable()
                    ->toggleable(),
                IconColumn::make('admin')
                    ->label('Admin')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Registriert am')
                    ->dateTime('d.m.Y H:i')
                    ->suffix(' Uhr')
                    ->sortable()
                    ->toggleable()
                    ->toggledHiddenByDefault(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('Noch keine Benutzer')
            ->emptyStateIcon('heroicon-o-users')
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('reset_data_lock')->label('Daten entsperren')->visible(fn (User $record) => $record->last_data_change?->isPast() ?? false)->action(function (User $user) {
                    $user->last_data_change = null;
                    $user->save();
                    Notification::make('data_lock_removed')->success()->title('Datensperre entfernt')->body('Die Datensperre wurde entfernt.')->send();
                })->requiresConfirmation(),
                Action::make('ban')
                    ->label('Bannen')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => ! $record->isBanned())
                    ->action(function (User $record): void {
                        $record->ban();
                        Notification::make()->title('Benutzer gebannt')->success()->send();
                    }),
                Action::make('unban')
                    ->label('Entbannen')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record) => $record->isBanned())
                    ->action(function (User $record): void {
                        $record->unban();
                        Notification::make()->title('Benutzer entbannt')->success()->send();
                    }),
                Action::make('impersonate')
                    ->label('Impersonieren')
                    ->icon('heroicon-o-finger-print')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Als Benutzer agieren')
                    ->modalDescription(fn (User $record): string => sprintf(
                        'Du wirst als "%s" eingeloggt. Die Aktion wird protokolliert und endet automatisch nach %d Minuten.',
                        $record->name,
                        ImpersonationService::MAX_DURATION_MINUTES,
                    ))
                    ->visible(fn (User $record): bool => ! $record->isAdmin()
                        && ! $record->isBanned()
                        && $record->isNot(Auth::user())
                        && ! ImpersonationService::isImpersonating())
                    ->action(function (User $record, Component $livewire): void {
                        try {
                            ImpersonationService::start(Auth::user(), $record);
                        } catch (AuthorizationException $exception) {
                            Notification::make()->title('Impersonation nicht möglich')->body($exception->getMessage())->danger()->send();

                            return;
                        }

                        $livewire->redirect('/pr0p0ll');
                    }),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profil')
                ->icon('heroicon-o-user-circle')
                ->columnSpanFull()
                ->schema([
                    Grid::make(['sm' => 1, 'md' => 2])->schema([
                        TextEntry::make('name')
                            ->label('Name')
                            ->icon('heroicon-o-user'),
                        TextEntry::make('email')
                            ->label('E-Mail')
                            ->icon('heroicon-o-envelope')
                            ->placeholder('—'),
                        IconEntry::make('email_verified_at')
                            ->label('E-Mail verifiziert')
                            ->boolean(),
                        IconEntry::make('admin')
                            ->label('Administrator')
                            ->boolean(),
                    ]),
                ]),

            Section::make('Aktivität')
                ->icon('heroicon-o-chart-bar')
                ->columnSpanFull()
                ->schema([
                    Grid::make(['sm' => 1, 'md' => 3])->schema([
                        TextEntry::make('polls_count')
                            ->label('Erstellte Umfragen')
                            ->icon('heroicon-o-clipboard-document-list')
                            ->state(fn (User $record): int => $record->polls()->count()),
                        TextEntry::make('participations_count')
                            ->label('Teilnahmen')
                            ->icon('heroicon-o-check-badge')
                            ->state(fn (User $record): int => $record->participations()->count()),
                        TextEntry::make('created_at')
                            ->label('Registriert')
                            ->icon('heroicon-o-calendar')
                            ->date('d.m.Y'),
                    ]),
                ]),

            Section::make('Antworten')
                ->description('Alle abgegebenen Antworten dieses Benutzers.')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->collapsible()
                ->collapsed()
                ->schema([
                    RepeatableEntry::make('answers')
                        ->hiddenLabel()
                        ->schema([
                            Grid::make(['sm' => 1, 'md' => 2])->schema([
                                TextEntry::make('poll.title')
                                    ->label('Umfrage')
                                    ->icon('heroicon-o-clipboard-document-list')
                                    ->columnSpanFull(),
                                TextEntry::make('question.title')
                                    ->label('Frage')
                                    ->icon('heroicon-o-question-mark-circle'),
                                TextEntry::make('answerable.answer_value')
                                    ->label('Antwort')
                                    ->icon('heroicon-o-chat-bubble-bottom-center-text'),
                                TextEntry::make('created_at')
                                    ->label('Abgegeben am')
                                    ->icon('heroicon-o-calendar')
                                    ->dateTime('d.m.Y H:i')
                                    ->suffix(' Uhr')
                                    ->columnSpanFull(),
                            ]),
                        ])
                        ->grid(['sm' => 1, 'md' => 2]),
                ])
                ->columnSpanFull()
                ->visible(fn (User $record): bool => $record->answers()->exists()),
        ])->columns(1);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'view' => ViewUser::route('/{record}'),
        ];
    }
}
