<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\Section;
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
                Section::make('WIP')->schema([
                ])->collapsible(),
                Section::make('Wie lege ich eine Umfrage an?')->schema([
                ])->collapsible(),
                Section::make('Wie lösche ich meinen Account?')->schema([
                ])->collapsible(),
                Section::make('Wann kann ich einen pr0gramm-Post erstellen?')->schema([
                ])->collapsible(),
            ]);
    }
}
