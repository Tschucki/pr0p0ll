<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Polls\MyPoll;
use App\Models\Polls\Poll;
use App\Models\Polls\PublicPoll;
use App\Policies\MyPollPolicy;
use App\Policies\PollPolicy;
use App\Policies\PublicPollPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        //
        MyPoll::class => MyPollPolicy::class,
        PublicPoll::class => PublicPollPolicy::class,
        Poll::class => PollPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        //
    }
}
