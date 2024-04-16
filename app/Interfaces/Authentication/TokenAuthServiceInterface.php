<?php

namespace App\Interfaces\Authentication;

use App\Models\User;

interface TokenAuthServiceInterface
{
    /**
     * Fetch a user with the email and password credentials
     */
    public function getUserViaEmailAndPassword(string $email, string $password): ?User;

    /**
     * Fetch the user with the mobile number and password credentials
     */
    public function getUserViaMobileNumberAndPassword(string $mobileNumber, string $password): ?User;

    /**
     * Create access token for the user
     */
    public function bindAuthToken(User $user, string $tokenName, int $expiresAtHours = 12, bool $withUserDetails = true): array;

    /**
     * Delete the current access token of a user
     */
    public function destroyCurrentAuthToken(User $user): User;

    /**
     * Fetch all active access tokens of a user
     */
    public function getUserAuthTokens(User $user): array;

    /**
     * Delete multiple access tokens of a user
     *
     * @return void
     */
    public function destroyAccessTokens(User $user, array $tokenIds): array;
}
