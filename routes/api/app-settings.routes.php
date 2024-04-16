<?php

use App\Enums\Permission;
use App\Http\Controllers\AppSettingController;

Route::controller(AppSettingController::class)->name('app-settings.')->group(function () {
    /** @uses AppSettingController::store */
    Route::middleware([
        'auth:sanctum',
        'verified.api',
        'permission:'.Permission::UPDATE_APP_SETTINGS->value,
    ])
        ->post('', 'store')->name('store');

    /** @uses AppSettingController::index */
    Route::get('', 'index')->name('index');
});
