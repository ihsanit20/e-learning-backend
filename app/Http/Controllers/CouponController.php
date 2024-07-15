<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Coupon;

class CouponController extends Controller
{
    public function index()
    {
        return Coupon::all();
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

        $coupon = Coupon::create($request->all());
        return response()->json($coupon, 201);
    }

    public function update(Request $request, Coupon $coupon)
    {
        $request->validate([
            'code' => 'required|string|unique:coupons,code,' . $coupon->id,
            'discount_type' => 'required|in:percentage,fixed',
            'discount_value' => 'required|integer',
            'valid_from' => 'required|date',
            'valid_until' => 'required|date',
        ]);

        $coupon->update($request->all());
        return response()->json($coupon);
    }

    public function destroy(Coupon $coupon)
    {
        $coupon->delete();
        return response()->json(null, 204);
    }
}
