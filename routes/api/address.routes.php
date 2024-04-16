<?php

use App\Http\Controllers\Address\BarangayController;
use App\Http\Controllers\Address\CityController;
use App\Http\Controllers\Address\ProvinceController;
use App\Http\Controllers\Address\RegionController;

Route::controller(RegionController::class)->group(function () {
    /** @uses RegionController::fetch */
    Route::get('regions', 'fetch')->name('regions.index');
});

Route::controller(ProvinceController::class)->group(function () {
    /** @uses ProvinceController::fetch */
    Route::get('provinces', 'fetch')->name('provinces.index');
});

Route::controller(CityController::class)->group(function () {
    /** @uses CityController::fetch */
    Route::get('cities', 'fetch')->name('cities.index');
});

Route::controller(BarangayController::class)->group(function () {
    /** @uses BarangayController::fetch */
    Route::get('barangays', 'fetch')->name('barangays.fetch');
});
