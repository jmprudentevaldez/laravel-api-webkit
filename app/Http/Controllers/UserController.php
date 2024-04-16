<?php

namespace App\Http\Controllers;

use App\Enums\ApiErrorCode;
use App\Enums\PaginationType;
use App\Events\UserCreated;
use App\Http\Requests\UserRequest;
use App\Interfaces\CloudFileServices\CloudFileServiceInterface;
use App\Interfaces\HttpResources\UserServiceInterface;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\JsonResponse;
use PaginationHelper;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\Response;

class UserController extends ApiController
{
    private UserServiceInterface $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Display a listing of users
     *
     *
     * @noinspection PhpUnusedParameterInspection
     */
    public function index(UserRequest $request): JsonResponse
    {
        $users = $this->userService->all(PaginationType::LENGTH_AWARE);
        $formatted = PaginationHelper::formatPagination($users);

        return $this->success($formatted, Response::HTTP_OK);
    }

    /**
     * Persist a user record
     */
    public function store(UserRequest $request): JsonResponse
    {
        // super_users cannot be created
        if ($this->rolesHaveSuperUser($request)) {
            return $this->error('A Super User cannot be created', Response::HTTP_FORBIDDEN, ApiErrorCode::BAD_REQUEST);
        }

        $user = $this->userService->create($request->validated());
        $temporaryPassword = $request->get('password');
        UserCreated::dispatch($user, $temporaryPassword);

        return $this->success(['data' => $user], Response::HTTP_CREATED);
    }

    /**
     * Fetch a single user's details
     */
    public function read($id): JsonResponse
    {
        $user = $this->userService->read($id);

        return $this->success(['data' => $user], Response::HTTP_OK);
    }

    /**
     * Update a user
     *
     * @throws AuthorizationException
     */
    public function update($id, UserRequest $request): JsonResponse
    {
        $user = $this->userService->read($id);
        $this->authorize('update', $user);
        $updatedUser = $this->userService->update($user, $request->validated());

        return $this->success(['data' => $updatedUser], Response::HTTP_OK);
    }

    /**
     * Delete a user
     *
     * @throws AuthorizationException
     */
    public function destroy($id): JsonResponse
    {
        $user = $this->userService->read($id);
        $this->authorize('delete', $user);
        $this->userService->destroy($user);

        return $this->success(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Upload profile picture
     */
    public function uploadProfilePicture($id, UserRequest $request, CloudFileServiceInterface $uploader): JsonResponse
    {
        $file = $request->file('photo');
        $result = $uploader->upload($id, $file, 'images', 'profile-pictures');
        $this->userService->update($id, ['profile_picture_path' => $result['path']]);

        return $this->success(['data' => $result], Response::HTTP_OK);
    }

    /**
     * Search for a user via name or email
     */
    public function search(UserRequest $request): JsonResponse
    {
        $users = $this->userService->search($request->get('query'), PaginationType::LENGTH_AWARE);
        $formatted = PaginationHelper::formatPagination($users);

        return $this->success($formatted, Response::HTTP_OK);
    }

    /**
     * Check if the provided roles have super_user
     */
    private function rolesHaveSuperUser(UserRequest $request): bool
    {
        $roles = $request->get('roles');
        $superAdminRole = Role::findByName(\App\Enums\Role::SUPER_USER->value, 'sanctum');

        return ! empty($roles) && in_array($superAdminRole->id, $roles);
    }
}
