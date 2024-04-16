<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Address\CityRequest;
use App\Models\Address\City;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class CityController extends ApiController
{
    /**
     * Retrieve all cities
     */
    public function fetch(CityRequest $request): JsonResponse
    {
        $cities = City::filtered()->orderBy('name')->get();

        return $this->success(['data' => $cities], Response::HTTP_OK);
    }
}
