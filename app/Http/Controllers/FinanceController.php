<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Illuminate\Http\Request;

class FinanceController extends Controller
{
    public function totalIncome(Request $request)
    {
        $query = Purchase::query();

        // কোর্স আইডি ফিল্টার (যদি প্রদান করা থাকে)
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // From date ফিল্টার
        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->from);
        }

        // To date ফিল্টার
        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->to);
        }

        // যদি শুধুমাত্র date প্যারামিটার থাকে (from/to না থাকলে)
        if ($request->filled('date') && !$request->filled('from') && !$request->filled('to')) {
            $query->whereDate('created_at', $request->date);
        }

        $totalIncome = $query->sum('paid_amount');

        return response()->json([
            'total_income' => $totalIncome
        ]);
    }

    public function MonthWiseTotalIncome()
    {
        $totalIncome = Purchase::query()
            ->selectRaw("SUM(paid_amount) as total_income, DATE_FORMAT(created_at, '%Y-%m') as month")
            ->groupBy('month')
            ->latest('month')
            ->get();
    
        return response()->json($totalIncome->map(function ($item) {
            return [
                'month' => $item->month,
                'total_income' => $item->total_income
            ];
        }));
    }
    
}
