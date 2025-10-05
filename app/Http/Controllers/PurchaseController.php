<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use Illuminate\Support\Facades\Cache;

class PurchaseController extends Controller
{
    public function getPurchasedCourses(Request $request)
    {
        $user = $request->user('sanctum');

        $limit = $request->input('limit');
        $withRelations = $request->boolean('with', true); // ডিফল্ট true
    
        $query = $user->courses();

        if ($request->status == 'active') {
            $query->active();
        } elseif ($request->status == 'inactive') {
            $query->active(0);
        }
    
        if ($withRelations) {
            $query->with('modules.lectures');
        }
    
        if ($limit) {
            $query->limit($limit);
        }
    
        $courses = $query->latest()->get();
    
        return response()->json($courses);
    }    

    public function getAllTransactions(Request $request)
    {
        try {
            $from = $request->has('from') ? date('Y-m-d 00:00:00', strtotime($request->from)) : now()->subDays(30)->format('Y-m-d 00:00:00');
            $to = $request->has('to') ? date('Y-m-d 23:59:59', strtotime($request->to)) : now()->format('Y-m-d 23:59:59');

            $cacheKey = 'transactions_' . md5(json_encode($request->all()));
            $purchases = Cache::remember($cacheKey, 60, function () use ($request, $from, $to) {
                return Purchase::query()
                    ->select([
                        'id',
                        'user_id',
                        'course_id',
                        'paid_amount',
                        'trx_id',
                        'coupon_code',
                        'discount_amount',
                        'created_at'
                    ])
                    ->with(['user:id,name,phone', 'course:id,title'])
                    ->when($request->status == 'paid', fn($query) => $query->where('paid_amount', '>', 0))
                    ->when($request->status == 'free', fn($query) => $query->where('paid_amount', '<=', 0))
                    ->whereBetween('created_at', [$from, $to])
                    ->when($request->filled('course_id'), fn($query) => $query->where('course_id', $request->course_id))
                    ->when($request->filled('trx_id'), fn($query) => $query->where('trx_id', 'like', '%' . $request->trx_id . '%'))
                    ->when($request->filled('phone'), fn($query) => $query->whereHas('user', fn($q) => $q->where('phone', 'like', '%' . $request->phone . '%')))
                    ->orderBy('created_at', 'desc')
                    ->paginate(50);
            });

            return response()->json($purchases);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve transactions.', 'message' => $e->getMessage()], 500);
        }
    }
}
