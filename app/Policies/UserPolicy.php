<?php

namespace App\Policies;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Superusers cannot be deleted
     */
    public function delete(User $user, User $targetUser): Response
    {
        if ($targetUser->hasRole(Role::SUPER_USER->value)) {
            return Response::deny('A super user cannot be deleted.');
        }

        return Response::allow();
    }

    /**
     * Superusers cannot be edited
     */
    public function update(User $user, User $targetUser): Response
    {
        if ($targetUser->hasRole(Role::SUPER_USER->value)) {
            return Response::deny('A super user cannot be updated.');
        }

        return Response::allow();
    }
}
