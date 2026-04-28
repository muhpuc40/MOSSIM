<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\CouponService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $service) {}

    /*
    |--------------------------------------------------------------------------
    | POST /api/v1/coupons/validate
    |
    | Body: { "code": "SAVE20", "order_amount": 1500.00 }
    |
    | Returns discount breakdown if valid.
    |--------------------------------------------------------------------------
    */
    public function validate(Request $request): JsonResponse
    {
        $request->validate([
            'code'         => ['required', 'string', 'max:30'],
            'order_amount' => ['required', 'numeric', 'min:0'],
        ]);

        $result = $this->service->validate($request->code, (float) $request->order_amount);

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success'         => true,
            'coupon_code'     => $result['coupon']->code,
            'discount_type'   => $result['coupon']->discount_type,
            'discount_value'  => $result['coupon']->discount_value,
            'discount_amount' => $result['discount_amount'],
            'final_amount'    => $result['final_amount'],
        ]);
    }
}