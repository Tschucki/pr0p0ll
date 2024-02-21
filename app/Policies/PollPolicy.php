<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Polls\Poll;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PollPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, Poll $poll): bool
    {
        return $user->isAdmin();
    }
}
