<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChapterController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\GalleryController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\LectureController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\McqOptionController;
use App\Http\Controllers\PhotoController;
use App\Http\Controllers\QuestionController;
use App\Http\Controllers\QuizController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\UserCourseExamController;
use App\Http\Controllers\UserQuizController;

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

Route::post('/send-otp', [AuthController::class, 'sendOtp']);
Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::get('/courses', [CourseController::class, 'index']);
Route::get('/courses/latest', [CourseController::class, 'latest']);
Route::get('/courses/{course}/not-enrolled-user-by-phone/{phone}', [CourseController::class, 'findNotEnrolledUserByPhone']);
Route::get('/courses/category/{category}', [CourseController::class, 'coursesByCategory']);
Route::get('/courses/{course}', [CourseController::class, 'show']);

Route::get('/category-list', [CategoryController::class, 'index']);
Route::get('category/{category}', [CategoryController::class, 'show']);

Route::get('/galleries', [GalleryController::class, 'index']);

Route::get('quiz', [UserQuizController::class, 'index']);
Route::get('quiz/{quiz}', [UserQuizController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/upload-photo', [PhotoController::class, 'upload']);

    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/user/photo', [UserController::class, 'uploadPhoto']);

    Route::post('/apply-affiliate', [UserController::class, 'applyAffiliate']);

    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);

    Route::middleware('course.purchased')->group(function () {
        Route::get('/my-courses/{course}', [CourseController::class, 'showPurchasedCourse']);
        Route::get('my-courses/{course}/exams/{exam}', [UserCourseExamController::class, 'fetchExamWithQuestion']);
        Route::post('my-courses/{course}/exams/{exam}', [UserCourseExamController::class, 'submitExamWithQuestion']);
    });

    Route::get('/my-quiz/{quiz}/participation', [UserQuizController::class, 'fetchQuizWithQuestion']);
    Route::post('/my-quiz/{quiz}/submit', [UserQuizController::class, 'submitQuizWithQuestion']);

    Route::get('/coupons/{code}', [CouponController::class, 'showByCode']);

    Route::get('/user-coupons', [CouponController::class, 'userCoupons']);
    Route::get('/user-earnings', [CouponController::class, 'userEarnings']);

    Route::post('courses/{course}/payment', [PaymentController::class, 'payment']);
    Route::post('courses/{course}/enroll', [PaymentController::class, 'enroll']);

    Route::get('/user/courses', [PurchaseController::class, 'getPurchasedCourses']);

    Route::post('lectures/{lecture}/complete', [LectureController::class, 'completeLecture']);
    Route::get('users/{user}/lectures/{lecture}/completion', [LectureController::class, 'getLectureCompletionStatus']);

    Route::middleware(['role:developer,admin'])->group(function () {
        Route::post('/users', [UserController::class, 'store']);
        Route::get('/users', [UserController::class, 'getUsers']);
        Route::put('/users/{user}', [UserController::class, 'update']);

        Route::post('/courses', [CourseController::class, 'store']);
        Route::put('/courses/{course}', [CourseController::class, 'update']);
        Route::patch('/courses/{course}/update-publish-status', [CourseController::class, 'updatePublishStatus']);
        Route::patch('/courses/{course}/update-active-status', [CourseController::class, 'updateActiveStatus']);
        Route::delete('/courses/{course}', [CourseController::class, 'destroy']);
        Route::post('/courses/{course}/thumbnail', [CourseController::class, 'uploadThumbnail']);

        Route::apiResource('category', CategoryController::class)->only(['index', 'store', 'show', 'update', 'destroy']);

        Route::post('category', [CategoryController::class, 'store']);
        Route::put('category/{category}', [CategoryController::class, 'update']);
        Route::delete('category/{category}', [CategoryController::class, 'destroy']);

        Route::get('/courses/{course}/modules', [ModuleController::class, 'index']);
        Route::apiResource('modules', ModuleController::class)->only(['store', 'show', 'update', 'destroy']);

        Route::get('/modules/{module}/lectures', [LectureController::class, 'index']);
        Route::post('/modules/{module}/lectures', [LectureController::class, 'store']);

        Route::get('/lectures/{lecture}', [LectureController::class, 'show']);

        Route::put('/modules/{module}/lectures/{lecture}', [LectureController::class, 'update']);
        Route::delete('/modules/{module}/lectures/{lecture}', [LectureController::class, 'destroy']);

        Route::get('/modules/{module}/materials', [MaterialController::class, 'index']);
        Route::post('/modules/{module}/materials', [MaterialController::class, 'store']);
        Route::put('/modules/{module}/materials/{material}', [MaterialController::class, 'update']);
        Route::delete('/modules/{module}/materials/{material}', [MaterialController::class, 'destroy']);

        Route::get('/coupons', [CouponController::class, 'index']);
        Route::post('/coupons', [CouponController::class, 'store']);
        Route::put('/coupons/{coupon}', [CouponController::class, 'update']);
        Route::delete('/coupons/{coupon}', [CouponController::class, 'destroy']);

        Route::get('/transactions', [PurchaseController::class, 'getAllTransactions']);

        Route::post('/galleries', [GalleryController::class, 'uploadPhoto']);
        Route::delete('/galleries/{id}', [GalleryController::class, 'destroy']);

        // Exam routes
        Route::get('/modules/{module}/exams', [ExamController::class, 'index']);
        Route::post('/modules/{module}/exams', [ExamController::class, 'store']);
        Route::get('/exams/{exam}', [ExamController::class, 'show']);
        Route::put('/exams/{exam}', [ExamController::class, 'update']);
        Route::delete('/modules/{module}/exams/{exam}', [ExamController::class, 'destroy']);
        Route::post('/exams/{exam}/select-questions/{question}', [ExamController::class, 'selectQuestion']);
        Route::delete('/exams/{exam}/remove-questions/{question}', [ExamController::class, 'removeQuestion']);
        Route::put('/exams/{exam}/change-question-mark', [ExamController::class, 'changeQuestionMark']);
        Route::get('/exams/{exam}/results', [ExamController::class, 'results']);

        // Quiz routes
        Route::post('/quizzes/{quiz}/select-questions/{question}', [QuizController::class, 'selectQuestion']);
        Route::delete('/quizzes/{quiz}/remove-questions/{question}', [QuizController::class, 'removeQuestion']);
        Route::put('/quizzes/{quiz}/change-question-mark', [QuizController::class, 'changeQuestionMark']);
        Route::get('/quizzes/{quiz}/results', [QuizController::class, 'results']);
        Route::apiResource('quizzes', QuizController::class);

        Route::apiResource('subjects', SubjectController::class);
        Route::apiResource('chapters', ChapterController::class);
        Route::apiResource('questions', QuestionController::class);
        Route::apiResource('mcq-options', McqOptionController::class);

        Route::get('/total-income', [FinanceController::class, 'totalIncome']);
        Route::get('/month-wise-total-income', [FinanceController::class, 'MonthWiseTotalIncome']);
        Route::get('/course-wise-monthly-incomes/{month}', [FinanceController::class, 'CourseWiseMonthlyIncome']);
    });

    Route::middleware(['role:developer,admin,mentor'])->group(function () {});

    Route::middleware(['role:developer'])->group(function () {});
});
