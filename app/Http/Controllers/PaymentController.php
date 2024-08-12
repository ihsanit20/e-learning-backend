<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use App\Models\Course;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Msilabs\Bkash\BkashPayment;

class PaymentController extends Controller
{
    use BkashPayment;

    public function payment(Request $request, Course $course)
    {
        $user = $request->user();

        // return $request;

        $discount = 0;

        $params = "";

        if($request->coupon_code) {
            // get discount by code
            $coupon = Coupon::where('code', $request->coupon_code)->first();

            if ($coupon) {
                if ($coupon->discount_type == 'percentage') {
                    $discount = ($course->price * $coupon->discount_value) / 100;
                } else {
                    $discount = $coupon->discount_value;
                }
                
                $params = "?coupon_code=" . $request->coupon_code;
            }
        }

        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'You have already purchased this course'], 400);
        }

        $payable = $course->price - $discount;

        if($payable <= 0) {
            Purchase::create([
                'user_id'           => $user->id,
                'course_id'         => $course->id,
                'paid_amount'       => 0,
                'trx_id'            => null,
                'discount_amount'   => $discount,
                'coupon_code'       => $request->coupon_code,
                'response'          => null,
            ]);

            return response([
                'data' => [
                    'bkashURL' => env('FRONTEND_BASE_URL', 'https://ciademy.com') . '/my/course',
                ]
            ]);
        }
   
        $invoice_id = $user->id . '-' . $course->id . '-' . time();

        $callbackUrl = env('FRONTEND_BASE_URL', 'https://ciademy.com') . "/checkout/{$course->id}/callback" . $params;

        $response = $this->createPayment($course->price - $discount, $invoice_id, $callbackUrl);

        return response([
            'data' => $response
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        $paymentID = $request->input('paymentID');

        $discount = 0;

        if($paymentID) {
            $response = $this->executePayment($paymentID);
      
            if($response->transactionStatus == 'Completed') {
                $user = $request->user();

                if($request->coupon_code) {
                    // get discount by code
                    $coupon = Coupon::where('code', $request->coupon_code)->first();
        
                    if ($coupon) {
                        if ($coupon->discount_type == 'percentage') {
                            $discount = ($course->price * $coupon->discount_value) / 100;
                        } else {
                            $discount = $coupon->discount_value;
                        }
                    }
                }

                Purchase::create([
                    'user_id'           => $user->id,
                    'course_id'         => $course->id,
                    'paid_amount'       => $response->amount,
                    'trx_id'            => $response->trxID,
                    'discount_amount'   => $discount,
                    'coupon_code'       => $request->coupon_code,
                    'response'          => $response,
                ]);

                return response()->json([
                    'message' => 'Course purchased successfully',
                    'status' => (boolean) (true),
                ], 201);
            } else {
                return response()->json([
                    'message' => 'Payment failed! Try Again!',
                    'status' => (boolean) (false),
                ], 200);
            }
        } else {
            return response()->json([
                'message' => 'Payment failed! Try Again',
                'status' => (boolean) (false),
            ], 200);
        }
    }
}
