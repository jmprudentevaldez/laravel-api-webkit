<?php

namespace App\Services\Authentication;

use App\Interfaces\Authentication\TokenAuthServiceInterface;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;

class TokenAuthService implements TokenAuthServiceInterface
{
    private User $model;

    public function __construct(User $user)
    {
        $this->model = $user;
    }

    /** {@inheritDoc} */
    public function getUserViaEmailAndPassword(string $email, string $password): ?User
    {
        $user = $this->model::where('email', $email)->first();
        $hasCorrectCreds = $user && Hash::check($password, $user->password);
        if (! $hasCorrectCreds) {
            return null;
        }

        return $user;
    }

    /** {@inheritDoc} */
    public function getUserViaMobileNumberAndPassword(string $mobileNumber, string $password): ?User
    {
        $user = User::query()
            ->join('user_profiles', 'user_profiles.user_id', '=', 'users.id')
            ->where('mobile_number', $mobileNumber)
            ->with('userProfile')
            ->first();

        $hasCorrectCreds = $user && Hash::check($password, $user->password);
        if (! $hasCorrectCreds) {
            return null;
        }

        return $user;
    }

    /** {@inheritDoc} */
    public function bindAuthToken(User $user, string $tokenName, int $expiresAtHours = 12, bool $withUserDetails = true): array
    {
        /**
         * We'll set the abilities to allow everything [*]. Authorization will be handled by Spatie
         *
         * @see https://spatie.be/docs/laravel-permission/v5/introduction
         */
        $expiresAt = now()->addHours($expiresAtHours);
        $token = $user->createToken($tokenName, ['*'], $expiresAt)->plainTextToken;

        $data = ['token' => $token, 'token_name' => $tokenName, 'expires_at' => $expiresAt];
        if ($withUserDetails) {
            $data['user'] = $user->fresh('userProfile');
        }

        return $data;
    }

    /** {@inheritDoc} */
    public function destroyCurrentAuthToken(User $user): User
    {
        $user->currentAccessToken()->delete();

        return $user;
    }

    public function getUserAuthTokens(User $user): array
    {
        return $user->tokens
            ->map(function (PersonalAccessToken $token) {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'expires_at' => $token->expires_at,
                    'last_used_at' => $token->last_used_at,
                    'created_at' => $token->created_at,
                ];
            })
            // only get un-expired tokens
            ->reject(fn (array $token) => now() >= $token['expires_at'])
            ->values()
            ->toArray();
    }

    public function destroyAccessTokens(User $user, array $tokenIds): array
    {
        // delete everything if they pass a star (*)
        if ($tokenIds === ['*']) {
            $user->tokens()->delete();

            return $tokenIds;
        }

        foreach ($tokenIds as $tokenId) {
            $user->tokens()->where('id', $tokenId)->delete();
        }

        return $tokenIds;
    }
}
