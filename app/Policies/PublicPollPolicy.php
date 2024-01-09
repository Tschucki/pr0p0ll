<?php

namespace App\Policies;

use App\Models\Polls\PublicPoll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PublicPollPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PublicPoll $publicPoll): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, PublicPoll $publicPoll): bool
    {
        return false;
    }

    public function delete(User $user, PublicPoll $publicPoll): bool
    {
        return false;
    }

    public function restore(User $user, PublicPoll $publicPoll): bool
    {
        return false;
    }

    public function forceDelete(User $user, PublicPoll $publicPoll): bool
    {
        return false;
    }
}
