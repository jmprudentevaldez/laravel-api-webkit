<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Address\RegionRequest;
use App\Models\Address\Region;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RegionController extends ApiController
{
    /**
     * Retrieve all regions
     */
    public function fetch(RegionRequest $request): JsonResponse
    {
        $regions = Region::filtered()->orderBy('name')->get();

        return $this->success(['data' => $regions], Response::HTTP_OK);
    }
}
