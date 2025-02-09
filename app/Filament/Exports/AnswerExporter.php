<?php

declare(strict_types=1);

namespace App\Filament\Exports;

use App\Models\Answer;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;

class AnswerExporter extends Exporter
{
    protected static ?string $model = Answer::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('id')
                ->label('ID'),
            ExportColumn::make('poll.id')->label('Poll_ID'),
            ExportColumn::make('question.title')->label('Question_Title'),
            ExportColumn::make('answerable_type')->label('Answer_Type'),
            ExportColumn::make('answerable.answer_value')->label('Answer_Value'),
            ExportColumn::make('anonymousUser.id')->label('Participator_ID'),
            ExportColumn::make('anonymousUser.age')->label('Participator_Age'),
            ExportColumn::make('anonymousUser.nationality')->label('Participator_Nationality'),
            ExportColumn::make('anonymousUser.gender')->label('Participator_Gender'),
            ExportColumn::make('anonymousUser.region')->label('Participator_Region'),
            ExportColumn::make('created_at')->label('Created_At'),
        ];
    }

    public function getFileName(Export $export): string
    {
        return 'poll-answers-export-'.now()->format('Y-m-d_H-i-s').'.csv';
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Dein Poll Export ist abgeschlossen. '.number_format($export->successful_rows).' '.str('row')->plural($export->successful_rows).' exportiert.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.str('row')->plural($failedRowsCount).' konnte nicht exportiert werden.';
        }

        return $body;
    }
}
