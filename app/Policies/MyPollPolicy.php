<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Polls\MyPoll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MyPollPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, MyPoll $myPoll): bool
    {
        return $myPoll->user_id === $user->getKey() || $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, MyPoll $myPoll): bool
    {
        if ($myPoll->isInReview() || $myPoll->isApproved()) {
            return false;
        }

        return $myPoll->user_id === $user->getKey() || $user->isAdmin();
    }

    public function delete(User $user, MyPoll $myPoll): bool
    {
        return $myPoll->user_id === $user->getKey() || $user->isAdmin();
    }

    public function restore(User $user, MyPoll $myPoll): bool
    {
        return $myPoll->user_id === $user->getKey() || $user->isAdmin();
    }

    public function forceDelete(User $user, MyPoll $myPoll): bool
    {
        return $myPoll->user_id === $user->getKey() || $user->isAdmin();
    }
}
