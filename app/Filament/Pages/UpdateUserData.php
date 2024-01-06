<?php

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Pages\Auth\EditProfile;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Profiler\Profile;

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
}
