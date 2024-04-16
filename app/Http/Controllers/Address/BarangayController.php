<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Address\BarangayRequest;
use App\Models\Address\Barangay;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class BarangayController extends ApiController
{
    /**
     * Retrieve all cities
     */
    public function fetch(BarangayRequest $request): JsonResponse
    {
        $cities = Barangay::filtered()->orderBy('name')->get();

        return $this->success(['data' => $cities], Response::HTTP_OK);
    }
}
