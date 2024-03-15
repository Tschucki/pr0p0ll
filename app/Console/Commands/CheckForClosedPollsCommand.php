<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendOwnPollClosedEmailNotification;
use App\Jobs\SendOwnPollClosedPr0GrammNotification;
use App\Models\Polls\Poll;
use Illuminate\Console\Command;

class CheckForClosedPollsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-for-closed-polls-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for closed polls and sends notifications to the users.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Poll::where('published_at', '<', now())->where('in_review', false)->where('visible_to_public', true)->each(function (Poll $poll) {
            if ($poll->isClosed()) {
                $this->info('Close '.$poll->title);
                $poll->update([
                    'visible_to_public' => false,
                ]);
                SendOwnPollClosedEmailNotification::dispatch($poll, $poll->user);
                SendOwnPollClosedPr0grammNotification::dispatch($poll, $poll->user);
            }
        });
    }
}
