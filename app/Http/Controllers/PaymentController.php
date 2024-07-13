<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Purchase;
use Illuminate\Http\Request;
use App\Services\BkashService;
use Msilabs\Bkash\BkashPayment;

class PaymentController extends Controller
{
    use BkashPayment;

    public function payment(Request $request, Course $course)
    {
        $user = $request->user();

        // Check if the user has already purchased the course
        if ($user->courses()->where('course_id', $course->id)->exists()) {
            return response()->json(['message' => 'You have already purchased this course'], 400);
        }

        // return
        $invoice_id = $user->id . '-' . $course->id . '-' . time();

        $response = $this->createPayment($course->price, $invoice_id, "https://ciademy.com/checkout/{$course->id}/callback");

        // { 
        //     "statusCode": "0000", 
        //     "statusMessage": "Successful", 
        //     "paymentID": "TR0011ON1565154754797", //life time 24 hours
        //     "bkashURL": "https://bkash.com/redirect/tokenized/?paymentID=TR0011O N1565154754797*********", 
        //     "callbackURL": "yourURL.com", 
        //     "successCallbackURL": "yourURL.com?paymentID=TR0011ON1565154754797&status=success", 
        //     "failureCallbackURL": "yourURL.com?paymentID=TR0011ON1565154754797&status=failure", 
        //     "cancelledCallbackURL": "yourURL.com?paymentID=TR0011ON1565154754797&status=cancel", 
        //     "amount": "500", 
        //     "intent": "sale", 
        //     "currency": "BDT", 
        //     "paymentCreateTime": "2019-08-07T11:12:34:978 GMT+0600", 
        //     "transactionStatus": "Initiated", 
        //     "merchantInvoiceNumber": "Inv0124" 
        // }

        return response([
            'data' => $response
        ]);
    }

    public function enroll(Request $request, Course $course)
    {
        $paymentID = $request->input('paymentID');

        if($paymentID) {
            $response = $this->executePayment($paymentID);
      
            if($response->transactionStatus == 'Completed') {
                $order_id = $response['merchantInvoiceNumber'];
                $trxID = $response['trxID'];

                $user = $request->user();

                // Create the purchase
                Purchase::create([
                    'user_id' => $user->id,
                    'course_id' => $course->id,
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
