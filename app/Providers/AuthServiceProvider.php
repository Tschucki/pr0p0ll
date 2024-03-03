<?php

declare(strict_types=1);

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Models\Polls\MyPoll;
use App\Models\Polls\Poll;
use App\Models\Polls\PublicPoll;
use App\Models\User;
use App\Policies\MyPollPolicy;
use App\Policies\PollPolicy;
use App\Policies\PublicPollPolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        MyPoll::class => MyPollPolicy::class,
        PublicPoll::class => PublicPollPolicy::class,
        Poll::class => PollPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        Gate::define('viewPulse', static function (User $user) {
            return $user->isAdmin();
        });
    }
}
