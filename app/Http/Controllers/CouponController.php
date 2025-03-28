<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;
use App\Models\Purchase;

class CouponController extends Controller
{
    public function index(Request $request)
    {
        return Coupon::query()
            ->with([
                'course:id,title'
            ])
            ->when($request->code_type, function ($query, $code_type) {
                $query->where('code_type', $code_type);
            })
            ->latest('id')
            ->get();
    }

    public function userCoupons(Request $request)
    {
        $user = $request->user('sanctum');

        return Coupon::query()
            ->where('affiliate_user_id', $user->id)
            ->where('valid_from', '<=', now())
            ->where('valid_until', '>=', now())
            ->get();
    }

    public function userEarnings(Request $request)
    {
        // $user = $request->user('sanctum');

        return Purchase::query()
            ->with([
                'course:id,title',
            ])
            ->when(!$request->code && !$request->user_id, function ($query) {
                $query->take(0);
            })
            ->when($request->code, function ($query, $code) {
                $query->where('coupon_code', $code);
            })
            ->when($request->user_id, function ($query, $user_id) {
                $coupon_codes = Coupon::query()
                    ->where('affiliate_user_id', $user_id)
                    ->pluck('code')
                    ->toArray();

                $query->whereIn('coupon_code', $coupon_codes);
            })
            ->get();
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code',
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date',
        ]);

        $coupon = Coupon::create([
            'code'              => $request->code,
            'course_id'         => $request->course_id ?? null,
            'code_type'         => $request->code_type,
            'affiliate_user_id' => $request->code_type == 'affiliate' ? $request->affiliate_user_id : null,
            'commission_value'  => $request->code_type == 'affiliate' ? $request->commission_value : 0,
            'discount_type'     => $request->discount_type,
            'discount_value'    => $request->discount_value,
            'valid_from'        => $request->valid_from,
            'valid_until'       => $request->valid_until,
        ]);

        $coupon->load('course:id,title');

        return response()->json($coupon, 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            // 'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date',
        ]);

        $coupon->update([
            'course_id'         => $request->course_id ?? null,
            'discount_type'     => $request->discount_type,
            'discount_value'    => $request->discount_value,
            'commission_value'  => $coupon->code_type == 'affiliate' ? $request->commission_value : 0,
            'valid_from'        => $request->valid_from,
            'valid_until'       => $request->valid_until,
        ]);

        $coupon->load('course:id,title');

        return response()->json($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(null, 204);
    }

    public function showByCode($code)
    {
        $coupon = Coupon::query()
            ->where('code', $code)
            ->whereDate('valid_from', '<=', now())
            ->whereDate('valid_until', '>=', now())
            ->first();

        $requestedCourseId = request()->course_id;

        if (
            !$coupon ||
            ($requestedCourseId && !$coupon->isApplicableForCourseId($requestedCourseId))
        ) {
            return response()->noContent();
        }

        return response()->json($coupon);
    }
}
