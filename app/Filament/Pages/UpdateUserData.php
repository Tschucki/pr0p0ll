<?php

namespace App\Filament\Pages;

use App\Enums\Gender;
use App\Enums\Nationality;
use App\Enums\Region;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Pages\Auth\EditProfile;
use Filament\Support\Facades\FilamentView;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

use function Filament\Support\is_app_url;

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
        try {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $this->callHook('afterValidate');

            $data = $this->mutateFormDataBeforeSave($data);

            $this->callHook('beforeSave');

            $this->handleRecordUpdate($this->getUser(), $data);

            $this->callHook('afterSave');
        } catch (\Throwable $throwable) {
            return;
        }

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        Validator::make($this->data, [
            'gender' => 'nullable|in:M,F',
            'birthday' => 'nullable|date',
            'nationality' => ['nullable', Rule::in(Nationality::cases())],
            'region' => ['nullable', Rule::in(Region::cases())],
        ]);

        $record->update($data);

        return $record;
    }

    /**
     * @throws \Exception
     */
    protected function fillForm(): void
    {
        $user = $this->getUser();

        $data = [
            'gender' => $user->gender,
            'birthday' => $user->birthday ? Carbon::parse($user->birthday)->format('d.m.Y') : null,
            'nationality' => $user->nationality,
            'region' => $user->region,
        ];

        $this->callHook('beforeFill');

        $data = $this->mutateFormDataBeforeFill($data);

        $this->form->fill($data);

        $this->callHook('afterFill');
    }
}
