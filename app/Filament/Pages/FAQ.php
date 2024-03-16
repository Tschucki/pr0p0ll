<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;

class FAQ extends Page
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $title = 'FAQ';

    protected static ?string $navigationGroup = 'Hilfe';

    protected static ?int $navigationSort = 200;

    protected static ?string $slug = 'faq';

    protected static string $view = 'filament.pages.f-a-q';

    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Warum kann ich meine demographischen Daten nicht ändern?')->schema([
                    TextEntry::make('demographic_data_info')->state('Du kannst deine demographischen Daten nur alle 2 Monate ändern. Das soll verhindern, dass Leute ihre Daten ständig ändern um an Umfragen teilzunehmen bei denen ihr Profil eigentlich nicht in die Zielgruppe fallen würden. Du siehst aber wie lange es dauert, bis du deine Daten wieder ändern kannst auf der "Einstellungen"-Seite.')->label(''),
                ])->collapsible(),
            ]);
    }
}
