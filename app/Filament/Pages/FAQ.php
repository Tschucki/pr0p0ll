<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Concerns\InteractsWithInfolists;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FAQ extends Page
{
    use InteractsWithForms;
    use InteractsWithInfolists;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $title = 'FAQ';

    protected static string|\UnitEnum|null $navigationGroup = 'Hilfe';

    protected static ?int $navigationSort = 200;

    protected static ?string $slug = 'faq';

    protected string $view = 'filament.pages.f-a-q';

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Warum kann ich meine demografischen Daten nicht ändern?')
                    ->icon('heroicon-o-identification')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('demographic_data_info')
                            ->hiddenLabel()
                            ->state('Demografische Daten lassen sich nur alle 2 Monate ändern. Das verhindert, dass Profile zwischen Zielgruppen wechseln und Umfrage-Ergebnisse verzerren. Auf der Einstellungen-Seite siehst du, wann die nächste Änderung möglich ist.'),
                    ]),

                Section::make('Wie funktioniert die Zielgruppe einer Umfrage?')
                    ->icon('heroicon-o-user-group')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('target_group_info')
                            ->hiddenLabel()
                            ->state('Beim Erstellen einer Umfrage definierst du optional eine Zielgruppe (Geschlecht, Alter, Nationalität). Nur passende Nutzer können teilnehmen. So entstehen aussagekräftigere Ergebnisse für die jeweilige Frage.'),
                    ]),

                Section::make('Sind meine Antworten anonym?')
                    ->icon('heroicon-o-lock-closed')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('anonymity_info')
                            ->hiddenLabel()
                            ->state('Antworten werden über einen anonymen Identifier abgelegt. Der Umfrage-Ersteller sieht ausschließlich aggregierte Ergebnisse, nicht deinen Account. Eine Ausnahme bilden Freitext-Antworten — sie werden mit deinem Account verknüpft, damit Spam und Beleidigungen moderiert werden können.'),
                    ]),

                Section::make('Wie melde ich einen Fehler oder eine Idee?')
                    ->icon('heroicon-o-bug-ant')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        TextEntry::make('contact_info')
                            ->hiddenLabel()
                            ->state('Auf GitHub im pr0p0ll-Repository kannst du Issues eröffnen. Alternativ erreichst du den Maintainer per pr0gramm-Nachricht (Link unten rechts in der Statistik-Übersicht).'),
                    ]),
            ]);
    }
}
