<?php

use App\Http\Controllers\AvailabilityController;

Route::controller(AvailabilityController::class)->name('availability.')->group(function () {
    /** @uses AvailabilityController::getEmailAvailability */
    Route::get('/email', 'getEmailAvailability')->name('email');

    /** @uses AvailabilityController::getMobileNumberAvailability */
    Route::get('/mobile_number', 'getMobileNumberAvailability')->name('mobile_number');
});
