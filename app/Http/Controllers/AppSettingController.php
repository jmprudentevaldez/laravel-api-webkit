<?php

namespace App\Http\Controllers;

use App\Http\Requests\AppSettingRequest;
use App\Models\AppSettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class AppSettingController extends ApiController
{
    /**
     * Fetch all the app settings
     */
    public function index(AppSettingRequest $request): JsonResponse
    {
        $settings = AppSettings::all();

        return $this->success(['data' => $settings], Response::HTTP_OK);
    }

    /**
     * Store the app settings (will overwrite the existing)
     *
     * @throws Throwable
     */
    public function store(AppSettingRequest $request): JsonResponse
    {
        $settings = DB::transaction(function () use ($request) {
            AppSettings::query()->delete();
            AppSettings::create([
                'name' => 'theme',
                'value' => $request->get('theme'),
            ]);

            return AppSettings::all();
        });

        return $this->success(['data' => $settings], Response::HTTP_CREATED);
    }
}
