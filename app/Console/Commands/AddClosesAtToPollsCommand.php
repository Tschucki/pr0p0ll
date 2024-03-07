<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Polls\PublicPoll;
use Illuminate\Console\Command;

class AddClosesAtToPollsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:add-closes-at-to-polls';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the closes_at column of public polls';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        PublicPoll::where('visible_to_public', true)->where('in_review', false)->where('approved', true)->where('closes_at', null)->each(function (PublicPoll $poll) {
            $this->info('Update '.$poll->title);
            $poll->update([
                'closes_at' => $poll->published_at->add($poll->closes_after),
            ]);
        });

        return 0;
    }
}
