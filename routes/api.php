<?php

use Illuminate\Support\Facades\Route;

/** V1 User resource routes */
Route::prefix('/v1/users')->group(base_path('routes/api/users.routes.php'));

/** V1 Auth Routes */
Route::prefix('/v1/auth')->group(base_path('routes/api/auth.routes.php'));

/** V1 Auth Routes */
Route::prefix('/v1/profile')->group(base_path('routes/api/profile.routes.php'));

/** V1 Availability Routes */
Route::prefix('/v1/availability')->group(base_path('routes/api/availability.routes.php'));

/** V1 Address */
Route::prefix('v1/address')->group(base_path('routes/api/address.routes.php'));

/** V1 Roles */
Route::prefix('/v1/roles')->group(base_path('routes/api/roles.routes.php'));

/** V1 App settings */
Route::prefix('/v1/app-settings')->group(base_path('routes/api/app-settings.routes.php'));
