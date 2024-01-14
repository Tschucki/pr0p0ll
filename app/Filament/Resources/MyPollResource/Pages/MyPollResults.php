<?php

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Resources\MyPollResource;
use App\Services\PollResultService;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Widgets\Widget;
use Filament\Widgets\WidgetConfiguration;

class MyPollResults extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MyPollResource::class;

    protected static string $view = 'filament.resources.my-poll-resource.pages.my-poll-results';

    protected static ?string $title = 'Ergebnisse';

    protected array $widgets = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        static::authorizeResourceAccess();

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    public function getWidgetData(): array
    {
        return [
            'poll' => $this->record,
        ];
    }

    public function getColumns(): int
    {
        return 1;
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getWidgets(): array
    {
        return (new PollResultService($this->record))->getAllWidgets();
    }

    /**
     * @return array<class-string<Widget> | WidgetConfiguration>
     */
    public function getVisibleWidgets(): array
    {
        return $this->filterVisibleWidgets($this->getWidgets());
    }
}
