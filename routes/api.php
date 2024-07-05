<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\BkashController;
use App\Http\Controllers\BkashTokenizePaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);
Route::post('check-phone', [AuthController::class, 'checkPhone']);

Route::get('/courses/latest', [CourseController::class, 'latest']);
Route::get('/courses/{course}', [CourseController::class, 'show']); // Public route

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    Route::post('/upload-profile-photo', [AuthController::class, 'uploadProfilePhoto']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store']);
    // Remove this line to avoid duplication and conflict
    // Route::get('/courses/{course}', [CourseController::class, 'show']); 
    Route::put('/courses/{course}', [CourseController::class, 'update']);
    Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
    
    Route::get('/courses/{course}/modules', [ModuleController::class, 'index']);
    Route::apiResource('modules', ModuleController::class)->only(['store', 'show', 'update', 'destroy']);

    Route::get('/modules/{module}/lectures', [LectureController::class, 'index']);
    Route::post('/modules/{module}/lectures', [LectureController::class, 'store']);
    Route::get('/lectures/{lecture}', [LectureController::class, 'show']);
    Route::put('/modules/{module}/lectures/{lecture}', [LectureController::class, 'update']);
    Route::delete('/modules/{module}/lectures/{lecture}', [LectureController::class, 'destroy']);

    Route::post('/purchase', [PurchaseController::class, 'purchaseCourse']);
    Route::get('/user/courses', [PurchaseController::class, 'getPurchasedCourses']);

    Route::post('/bkash/payment/create', [BkashController::class, 'createPayment']);
    Route::post('/bkash/payment/execute', [BkashController::class, 'executePayment']);

    Route::prefix('bkash')->group(function () {
        // Payment Routes for bKash
        Route::get('/payment', [BkashTokenizePaymentController::class, 'index']);
        Route::get('/create-payment', [BkashTokenizePaymentController::class, 'createPayment'])->name('bkash-create-payment');
        Route::get('/callback', [BkashTokenizePaymentController::class, 'callBack'])->name('bkash-callBack');
    
        // Search payment
        Route::get('/search/{trxID}', [BkashTokenizePaymentController::class, 'searchTnx'])->name('bkash-search');
    
        // Refund payment routes
        Route::get('/refund', [BkashTokenizePaymentController::class, 'refund'])->name('bkash-refund');
        Route::get('/refund/status', [BkashTokenizePaymentController::class, 'refundStatus'])->name('bkash-refund-status');
    });
});
