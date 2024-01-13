<?php

namespace App\Filament\Resources\PublicPollsResource\Pages;

use App\Filament\Resources\PublicPollsResource;
use App\Models\Polls\PublicPoll;
use App\Models\Question;
use App\Services\PollFormService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Exceptions\Halt;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\DB;
use Yepsua\Filament\Forms\Components\Rating;

use function Filament\Support\is_app_url;

/**
 * @property Form $form
 */
class PollParticipation extends Page
{
    use InteractsWithFormActions;
    use InteractsWithForms;
    use InteractsWithRecord;

    protected static string $resource = PublicPollsResource::class;

    protected static string $view = 'filament.resources.public-polls-resource.pages.poll-participation';

    /**
     * @var array<string, mixed> | null
     */
    public ?array $data = [];

    public function getTitle(): string|\Illuminate\Contracts\Support\Htmlable
    {
        return 'An '.'"'.$this->record->title.'"'.' teilnehmen';
    }

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->authorizeAccess();
        $this->form->fill();
    }

    protected function addRatingToForm(): Rating
    {
        return Rating::make('rating')->label('Wie würdest du die Umfrage bewerten?')->min(1)->max(5);
    }

    public function participate(): void
    {
        try {
            $this->callHook('beforeValidate');

            $data = $this->form->getState();

            $uniqueUserIdentifier = \Str::uuid()->toString();
            $tempData = array_unique($data);
            unset($tempData['rating']);
            $questionKeys = collect(array_keys($tempData));
            /**
             * @var PublicPoll $currentPublicPoll
             * */
            $currentPublicPoll = $this->record;
            $currentPollQuestions = $currentPublicPoll->questions()->pluck('id')->toArray();

            $areAllIdsInCurrentPoll = $questionKeys->diff($currentPollQuestions)->isEmpty();

            if (! $areAllIdsInCurrentPoll) {
                Notification::make('error')->danger()->title('Das ist ja komisch')->body('Du hast mehr beantwortet, als es Fragen gibt...')->send();
                throw new Halt('Du hast mehr beantwortet, als es Fragen gibt...');
            }
            DB::transaction(function () use ($tempData, $uniqueUserIdentifier) {
                collect(array_unique($tempData))->each(function ($answer, $key) use ($uniqueUserIdentifier) {
                    $question = Question::find($key);

                    if (! $question) {
                        Notification::make('error')->danger()->title('Fehler beim Speichern')->body("Die Frage mit der ID ${key} konnte nicht gefunden werden.")->send();
                        DB::rollBack();
                        throw new Halt("Die Frage mit der ID ${key} konnte nicht gefunden werden.");
                    }

                    $answerType = $question->answerType()->create([
                        'answer_value' => $answer,
                    ]);

                    $question->answers()->create([
                        'answerable_id' => $answerType->id,
                        'answerable_type' => get_class($answerType),
                        'user_id' => null,
                        'poll_id' => $this->getPoll()->getKey(),
                        'user_identifier' => $uniqueUserIdentifier,
                    ]);
                });
                DB::commit();
            });
            $this->getPoll()->participants()->attach(\Auth::id(), [
                'rating' => (int) $data['rating'],
            ]);
            $this->callHook('afterSave');
        } catch (Halt $exception) {
            return;
        }

        $this->getSavedNotification()?->send();

        if ($redirectUrl = $this->getRedirectUrl()) {
            $this->redirect($redirectUrl, navigate: FilamentView::hasSpaMode() && is_app_url($redirectUrl));
        }
    }

    public function getSavedNotification(): Notification
    {
        return Notification::make('saved')->success()->title('Antworten abgegeben')->body('Danke für die Teilnahme mein Süßer');
    }

    public function getRedirectUrl(): string
    {
        return route('filament.pr0p0ll.resources.public-polls.index');
    }

    public function form(Form $form): Form
    {
        $pollFormService = new PollFormService($this->record);
        $schema = $pollFormService->buildForm();
        $schema[] = $this->addRatingToForm();

        return $form
            ->schema($schema)
            ->statePath('data');
    }

    protected function getPoll(): PublicPoll|int|string
    {
        return $this->record;
    }

    /**
     * @return array<Action | ActionGroup>
     */
    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction(),
            $this->getCancelFormAction(),
        ];
    }

    protected function getCancelFormAction(): Action
    {
        return $this->backAction();
    }

    protected function getSaveFormAction(): Action
    {
        return Action::make('participate')
            ->label('Antworten abgeben')
            ->requiresConfirmation()
            ->submit('participate')
            ->keyBindings(['mod+s']);
    }

    /**
     * @deprecated Use `getCancelFormAction()` instead.
     */
    public function backAction(): Action
    {
        return Action::make('back')
            ->label('Zurück zur Übersicht')
            ->url(route('filament.pr0p0ll.resources.public-polls.index'))
            ->color('gray');
    }

    protected function authorizeAccess(): void
    {
        static::authorizeResourceAccess();

        abort_unless(static::getResource()::canView($this->getRecord()), 403);
    }
}