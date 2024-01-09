<?php

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Auth\EditProfile;
use Illuminate\Contracts\Support\Htmlable;

class UpdateUserData extends EditProfile
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public function getTitle(): string|Htmlable
    {
        return 'Meine Daten';
    }

    /**
     * @throws \Exception
     */
    protected function getForms(): array
    {
        return [
            'form' => $this->form(
                $this->makeForm()
                    ->schema([
                        Select::make('gender')->label('Geschlecht')->options(Gender::class),
                        DatePicker::make('birthday')
                            ->label('Geburtstag')
                            ->nullable()
                            ->before('today')
                            ->displayFormat('d.m.Y'),
                        Select::make('nationality')->label('Nationalität')->options(Nationality::class),
                        Select::make('region')->label('Region')->options(Region::class),
                    ])
                    ->operation('edit')
                    ->model($this->getUser())
                    ->statePath('data'),
            ),
        ];
    }

    public function save(): void
    {
        // TODO: Add own save as some props are hidden
        dd('TODO');
    }
}
