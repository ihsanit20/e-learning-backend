<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Carbon\Carbon;
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

    public function CourseWiseMonthlyIncome($month)
    {
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return response()->json(['error' => 'Invalid month format. Expected YYYY-MM'], 422);
        }

        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->toDateString();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->toDateString();

        $courseWisePurchases = Purchase::query()
            ->join('courses', 'purchases.course_id', '=', 'courses.id')
            ->selectRaw('courses.id as course_id, courses.title as course_name, SUM(purchases.paid_amount) as total_income')
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('courses.id')
            ->get();

        return response()->json($courseWisePurchases);
    }

}
