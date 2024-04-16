<?php

use App\Http\Controllers\AuthController;

/**
 *  This uses Laravel 9 Route group controllers
 *
 *  @see https://laravel.com/docs/9.x/routing#route-group-controllers
 */
Route::controller(AuthController::class)->group(function () {
    /** @uses AuthController::store */
    Route::middleware(['throttle:10,1'])->post('tokens', 'store')->name('auth.store');

    /** @uses AuthController::destroy */
    Route::middleware(['auth:sanctum', 'verified.api'])->delete('tokens', 'destroy')->name('auth.destroy');

    /** @uses AuthController::fetch */
    Route::middleware(['auth:sanctum', 'verified.api'])->get('tokens', 'fetch')->name('auth.fetch');

    /** @uses AuthController::revoke */
    Route::middleware(['auth:sanctum', 'verified.api'])->post('tokens/revoke', 'revoke')->name('auth.revoke');

    /** @uses AuthController::register */
    Route::middleware(['throttle:10,1'])->post('register', 'register')->name('auth.register');

    /** @uses AuthController::forgotPassword */
    Route::post('forgot-password', 'forgotPassword')->name('auth.password.forgot');

    /** @uses AuthController::resetPassword */
    Route::post('reset-password', 'resetPassword')->name('auth.password.reset');

    /** @uses AuthController::resendEmailVerification */
    Route::middleware(['auth:sanctum'])
        ->get('email/send-verification', 'resendEmailVerification')
        ->name('auth.verification.resend');

    /** @uses AuthController::verifyEmail */
    Route::middleware(['signed:relative'])
        ->get('email/verify/{id}/{hash}', 'verifyEmail')
        ->name('verification.verify');
});
