<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\Purchase;
use App\Models\User;
use Illuminate\Http\Request;
use Msilabs\Bkash\BkashPayment;

class BundlePaymentController extends Controller
{
    use BkashPayment;

    public function findNotEnrolledUserByPhone(Request $request, Bundle $bundle, $phone)
    {
        $user = User::query()
            ->whereIn('phone', ["+88{$phone}", $phone])
            ->first();
            
        if (!$user) {
            return response()->json([
                'message' => "User not found with this phone number: {$phone}"
            ], 404);
        }

        $courseIds = $bundle->bundleCourses()->pluck('course_id');

        $already_purchased = Purchase::query()
            ->where('user_id', $user->id)
            ->whereIn('course_id', $courseIds)
            ->exists();

        if ($already_purchased) {
            return response()->json([
                'message' => 'This user has already purchased one or more courses from this bundle.'
            ], 404);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'photo' => $user->photo,
        ]);
    }

    public function payment(Request $request, Bundle $bundle)
    {
        $user = User::find($request->user_id) ?? $request->user();

        $bundle->load('bundleCourses');

        $alreadyPurchased = $user->courses()
            ->whereIn('course_id', $bundle->bundleCourses->pluck('course_id'))
            ->count();

        if ($alreadyPurchased > 0) {
            return response()->json([
                "message" => "You have already purchased {$alreadyPurchased} course/s"
            ], 400);
        }

        $invoiceId = "{$user->id}-{$bundle->id}-" . time();

        $callbackUrl = sprintf(
            "%s/bundle-checkout/%d/callback?user_id=%d",
            env('FRONTEND_BASE_URL', 'https://ciademy.com'),
            $bundle->id,
            $user->id
        );

        $payableAmount = $bundle->bundleCourses->sum('course_price');

        $response = $this->createPayment(
            $payableAmount,
            $invoiceId,
            $callbackUrl
        );

        return response()->json(['data' => $response]);
    }

    public function enroll(Request $request, Bundle $bundle)
    {
        $paymentID = $request->input('paymentID');
        if (!$paymentID) {
            return $this->paymentFailed('Payment failed! Try again');
        }

        $response = $this->executePayment($paymentID);
        if ($response->transactionStatus !== 'Completed') {
            return $this->paymentFailed('Payment failed! Try again!');
        }

        $authUser = $request->user();

        $user = User::find($request->user_id) ?? $authUser;

        $bundle->load(['bundleCourses.course:id,price']);

        foreach ($bundle->bundleCourses as $bundleCourse) {
            $course = $bundleCourse->course;
            if (!$course) continue;

            $discount = max(0, $course->price - $bundleCourse->course_price);

            Purchase::create([
                'user_id'           => $user->id,
                'auth_id'           => $authUser->id,
                'course_id'         => $bundleCourse->course_id,
                'paid_amount'       => $bundleCourse->course_price,
                'trx_id'            => "{$response->trxID}|{$response->amount}TK|{$bundleCourse->course_id}",
                'discount_amount'   => $discount,
                'commission_amount' => 0,
                'coupon_code'       => "Bundle:{$bundle->id}",
                'response'          => $response,
            ]);
        }

        return response()->json([
            'message' => 'Course purchased successfully',
            'status' => true,
        ], 201);
    }

    /**
     * Common reusable response for payment failure
     */
    private function paymentFailed(string $message)
    {
        return response()->json([
            'message' => $message,
            'status' => false,
        ], 200);
    }

}
