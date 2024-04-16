<?php

namespace App\Http\Controllers\Address;

use App\Http\Controllers\ApiController;
use App\Http\Requests\Address\ProvinceRequest;
use App\Models\Address\Province;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ProvinceController extends ApiController
{
    /**
     * Retrieve all provinces
     */
    public function fetch(ProvinceRequest $request): JsonResponse
    {
        $provinces = Province::filtered()->orderBy('name')->get();

        return $this->success(['data' => $provinces], Response::HTTP_OK);
    }
}
