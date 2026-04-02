<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\StatsController;



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
Route::post('/set-password', [AuthController::class, 'setPassword']);

Route::get('/email/verify/{id}/{hash}', function ($id, $hash, Request $request) {

    $user = User::findOrFail($id);

    // Validate hash
    if (! hash_equals(sha1($user->getEmailForVerification()), $hash)) {
        abort(403, 'Invalid verification link.');
    }

    if (! $user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }

    return redirect(config('app.frontend_url') . '/login?verified=1');

})->middleware(['signed'])->name('verification.verify');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('profile/update', [AuthController::class, 'updateProfile']);
    Route::post('/updatepassword', [AuthController::class, 'updatePassword']);
    Route::resource('users', UserController::class);
    Route::get('/roles', [RoleController::class, 'index']);
    Route::resource('contacts', ContactController::class);
    Route::resource('leads', LeadController::class);
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
    Route::resource('deals', DealController::class);
    Route::patch('deals/{deal}/move', [DealController::class, 'move']);
    Route::resource('tasks', TaskController::class);
    Route::apiResource('activities', ActivityController::class)->only(['index', 'store']);
    Route::get('stats', [StatsController::class, 'index']);



});
