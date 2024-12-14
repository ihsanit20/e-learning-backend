<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function getPurchasedCourses(Request $request)
    {
        $user = $request->user();
        $courses = $user->courses()->with('modules.lectures')->get();
        return response()->json($courses);
    }

    public function getAllTransactions()
    {
        try {
            $purchases = Purchase::query()
                ->with('user', 'course')
                ->when(request()->paid, function ($query) {
                    $query->where('paid_amount', '>', 0);
                })
                ->when(request()->free, function ($query) {
                    $query->where('paid_amount', '<=', 0);
                })
                ->get();

            return response()->json($purchases);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transactions.'], 500);
        }
    }
    
}