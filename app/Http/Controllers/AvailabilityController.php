<?php

namespace App\Http\Controllers;

use App\Http\Requests\AvailabilityRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AvailabilityController extends ApiController
{
    /**
     * Get email availability
     */
    public function getEmailAvailability(AvailabilityRequest $request): JsonResponse
    {
        $email = strtolower($request->get('value'));
        $excludedId = $request->get('excluded_id');
        $query = User::whereEmail($email);

        if ($excludedId) {
            $query->whereNot('id', $excludedId);
        }

        $isAvailable = ! $query->first();
        $data = ['is_available' => $isAvailable];

        return $this->success(['data' => $data], Response::HTTP_OK);
    }

    public function getMobileNumberAvailability(AvailabilityRequest $request): JsonResponse
    {
        $mobileNumber = $request->get('value');
        $excludedId = $request->get('excluded_id');
        $query = User::join('user_profiles', 'users.id', '=', 'user_profiles.user_id')
            ->where('user_profiles.mobile_number', '=', $mobileNumber);

        if ($excludedId) {
            $query->whereNot('users.id', $excludedId);
        }

        $isAvailable = ! $query->first();
        $data = ['is_available' => $isAvailable];

        return $this->success(['data' => $data], Response::HTTP_OK);
    }
}
