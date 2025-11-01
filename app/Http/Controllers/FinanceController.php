<?php

namespace App\Http\Controllers;

use App\Models\Purchase;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    /**
     * 🔹 Get total income with optional filters (course_id, from/to/date)
     */
    public function totalIncome(Request $request)
    {
        $query = Purchase::query();

        // ✅ কোর্স ফিল্টার
        if ($request->filled('course_id')) {
            $query->where('course_id', $request->course_id);
        }

        // ✅ তারিখ ফিল্টার (from / to)
        if ($request->filled('from') && $request->filled('to')) {
            $from = Carbon::parse($request->from)->startOfDay();
            $to   = Carbon::parse($request->to)->endOfDay();

            $query->whereBetween('created_at', [$from, $to]);
        } elseif ($request->filled('from')) {
            $query->where('created_at', '>=', Carbon::parse($request->from)->startOfDay());
        } elseif ($request->filled('to')) {
            $query->where('created_at', '<=', Carbon::parse($request->to)->endOfDay());
        } elseif ($request->filled('date')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->date)->startOfDay(),
                Carbon::parse($request->date)->endOfDay(),
            ]);
        }

        $totalIncome = $query->sum('paid_amount');

        return response()->json([
            'total_income' => $totalIncome,
        ]);
    }

    /**
     * 🔹 Get month-wise total income summary
     */
    public function MonthWiseTotalIncome()
    {
        $totalIncome = Purchase::query()
            ->selectRaw("
                DATE_FORMAT(created_at, '%Y-%m') as month,
                SUM(paid_amount) as total_income
            ")
            ->where('paid_amount', '>', 0)
            ->groupBy('month')
            ->orderByDesc(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->get();

        return response()->json($totalIncome);
    }

    /**
     * 🔹 Get course-wise income for a specific month
     */
    public function CourseWiseMonthlyIncome($month)
    {
        // ✅ মাস ফরম্যাট যাচাই (YYYY-MM)
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            return response()->json(['error' => 'Invalid month format. Expected YYYY-MM'], 422);
        }

        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth()->startOfDay();
        $endDate   = Carbon::createFromFormat('Y-m', $month)->endOfMonth()->endOfDay();

        $courseWisePurchases = Purchase::query()
            ->join('courses', 'purchases.course_id', '=', 'courses.id')
            ->selectRaw('
                courses.id as course_id,
                courses.title as course_name,
                COUNT(purchases.id) as total_purchases,
                SUM(purchases.paid_amount) as total_income
            ')
            ->where('purchases.paid_amount', '>', 0)
            ->whereBetween('purchases.created_at', [$startDate, $endDate])
            ->groupBy('courses.id', 'courses.title')
            ->orderByDesc('total_income')
            ->get();

        return response()->json($courseWisePurchases);
    }
}
