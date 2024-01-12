<?php

namespace App\Filament\Resources\MyPollResource\Pages;

use App\Filament\Resources\MyPollResource;
use App\Filament\Widgets\NeedsDataReviewWidget;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class MyPollResults extends Page
{
    use InteractsWithRecord;

    protected static string $resource = MyPollResource::class;

    protected static string $view = 'filament.resources.my-poll-resource.pages.my-poll-results';

    protected static ?string $title = 'Ergebnisse';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        static::authorizeResourceAccess();

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }

    protected function getHeaderWidgets(): array
    {
        return [
            NeedsDataReviewWidget::make(),
        ];
    }
}
