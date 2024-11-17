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
            ->when($request->code_type, function ($query, $code_type) {
                $query->where('code_type', $code_type);
            })
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
            ->when($request->code, function ($query, $code) {
                $query->where('coupon_code', $code);
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
            'code_type'         => $request->code_type,
            'affiliate_user_id' => $request->code_type == 'affiliate' ? $request->affiliate_user_id : null,
            'commission_value'  => $request->code_type == 'affiliate' ? $request->commission_value : 0,
            'discount_type'     => $request->discount_type,
            'discount_value'    => $request->discount_value,
            'valid_from'        => $request->valid_from,
            'valid_until'       => $request->valid_until,
        ]);

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
            'discount_type'     => $request->discount_type,
            'discount_value'    => $request->discount_value,
            'commission_value'  => $coupon->code_type == 'affiliate' ? $request->commission_value : 0,
            'valid_from'        => $request->valid_from,
            'valid_until'       => $request->valid_until,
        ]);

        return response()->json($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(null, 204);
    }

    public function showByCode($code)
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found'], 404);
        }

        return response()->json($coupon);
    }
}
