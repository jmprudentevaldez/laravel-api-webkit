<?php

namespace App\Services\HttpResources;

use App\Enums\PaginationType;
use App\Enums\Role;
use App\Interfaces\HttpResources\UserServiceInterface;
use App\Models\User;
use Carbon\Carbon;
use Hash;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Throwable;

class UserService extends HttpService implements UserServiceInterface
{
    public const MAX_TRANSACTION_DEADLOCK_ATTEMPTS = 5;

    private User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /** {@inheritDoc} */
    public function all(?PaginationType $pagination = null): Collection|Paginator|LengthAwarePaginator|CursorPaginator
    {
        /** @var Builder $users */
        $users = $this->model->filtered();

        return $this->buildPagination($pagination, $users);
    }

    /**
     * {@inheritDoc}
     * test
     *
     * @throws Throwable
     */
    public function create(array $userInfo): User
    {
        return DB::transaction(function () use ($userInfo) {
            $userCredentials = [
                'email' => $userInfo['email'],
                'password' => $userInfo['password'],
            ];

            if (isset($userInfo['active'])) {
                $userCredentials['active'] = $userInfo['active'];
            }

            if (isset($userInfo['email_verified'])) {
                $userCredentials['email_verified_at'] = $userInfo['email_verified'] ? Carbon::now('utc') : null;
            }

            $user = $this->model::create($userCredentials);

            $userRoles = empty($userInfo['roles']) ? [Role::STANDARD_USER->value] : $userInfo['roles'];
            $user->syncRoles($userRoles);
            $user = $user->fresh();

            // Set the user profile fields
            $exemptedAttributes = [
                'email',
                'password',
                'active',
                'email_verified_at',
                'home_address',
                'barangay_id',
                'city_id',
                'province_id',
                'region_id',
                'postal_code',
            ];
            $user->userProfile()->create(Arr::except($userInfo, $exemptedAttributes));

            // Set the Address fields
            $user->userProfile->address()->create(Arr::only(
                $userInfo,
                ['home_address', 'barangay_id', 'city_id', 'province_id', 'region_id', 'postal_code']
            ));

            return $user->load('userProfile');
        }, self::MAX_TRANSACTION_DEADLOCK_ATTEMPTS);
    }

    /** {@inheritDoc} */
    public function read($id): User
    {
        /** @var User $user */
        $user = $this->model::with('userProfile')->findOrFail($id);

        return $user;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Throwable
     */
    public function update(User|int|string $modelOrId, array $newUserInfo): User
    {
        return DB::transaction(function () use ($modelOrId, $newUserInfo) {
            $user = $modelOrId;
            if (! ($user instanceof User)) {
                $user = $this->model::findOrFail($modelOrId);
            }

            unset($newUserInfo['password_confirmation']);

            if (isset($newUserInfo['email_verified'])) {
                $newUserInfo['email_verified_at'] = $newUserInfo['email_verified'] ? Carbon::now('utc') : null;
                unset($newUserInfo['email_verified']);
            }

            $user->update(Arr::only($newUserInfo, ['email', 'password', 'active', 'email_verified_at']));
            $user->userProfile()->update(
                Arr::except($newUserInfo, ['email', 'password', 'active', 'email_verified_at', 'roles', 'home_address',
                    'barangay_id', 'city_id', 'province_id', 'region_id', 'postal_code',
                ])
            );

            // Update the address fields
            $user->userProfile->address()->update(Arr::only(
                $newUserInfo,
                ['home_address', 'barangay_id', 'city_id', 'province_id', 'region_id', 'postal_code']
            ));

            if (isset($newUserInfo['roles'])) {
                $user->syncRoles($newUserInfo['roles']);
            }

            return $user->fresh('userProfile');
        }, self::MAX_TRANSACTION_DEADLOCK_ATTEMPTS);
    }

    /**
     * Search user via email, last_name, first_name, and middle_name
     */
    public function search(
        string $term,
        ?PaginationType $pagination = null
    ): Collection|Paginator|LengthAwarePaginator|CursorPaginator {
        $users = $this->model::query()
            ->with('userProfile')
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')

            // Do a prefix match for email to preserve indexing performance
            ->where('users.email', 'like', "$term%")

            // Do a full match search for the names as they have a fullText index in our migrations
            ->orWhere('user_profiles.first_name', 'like', "%$term%")
            ->orWhere('user_profiles.last_name', 'like', "%$term%")
            ->orWhere('user_profiles.middle_name', 'like', "%$term%")
            ->orWhere('user_profiles.ext_name', 'like', "%$term%");

        return $this->buildPagination($pagination, $users);
    }

    /** {@inheritDoc} */
    public function destroy(User|int|string $modelOrId): User
    {
        $user = $modelOrId;
        if (! ($user instanceof User)) {
            $user = $this->model::findOrFail($modelOrId);
        }

        $user->delete();

        return $user;
    }

    public function updatePassword(User|int|string $modelOrId, string $newPassword, string $oldPassword): ?User
    {
        $user = $modelOrId;
        if (! ($user instanceof User)) {
            $user = $this->model::findOrFail($modelOrId);
        }

        if (! Hash::check($oldPassword, $user->password)) {
            return null;
        }
        $user->password = $newPassword;
        $user->save();

        return $user;
    }
}
