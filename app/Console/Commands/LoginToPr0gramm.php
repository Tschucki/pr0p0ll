<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Tschucki\Pr0grammApi\Facades\Pr0grammApi;

class LoginToPr0gramm extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:login-to-pr0gramm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Logs in to pr0gramm.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $login = Pr0grammApi::login(config('services.pr0gramm.username'), config('services.pr0gramm.password'));
        if (isset($login['success'])) {
            $this->info('Login successful');

            return 0;
        }

        $this->error('Login failed');

        return 1;
    }
}
