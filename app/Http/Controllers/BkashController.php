<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\BkashService;

class BkashController extends Controller
{
    protected $bkashService;

    public function __construct(BkashService $bkashService)
    {
        $this->bkashService = $bkashService;
    }

    public function createPayment(Request $request)
    {
        $result = $this->bkashService->createPayment($request->amount, $request->invoice_number);
        return response()->json($result);
    }

    public function executePayment(Request $request)
    {
        $result = $this->bkashService->executePayment($request->payment_id);
        return response()->json($result);
    }
}
