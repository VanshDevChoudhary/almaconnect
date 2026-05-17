<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;

class JobPolicy
{
    public function update(User $user, Job $job): bool
    {
        return $user->id === $job->posted_by || $user->isAdmin();
    }

    public function delete(User $user, Job $job): bool
    {
        return $this->update($user, $job);
    }

    public function markFilled(User $user, Job $job): bool
    {
        return $this->update($user, $job);
    }
}
