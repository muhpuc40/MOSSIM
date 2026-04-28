<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Coupon\StoreCouponRequest;
use App\Http\Requests\Admin\Coupon\UpdateCouponRequest;
use App\Models\Coupon;
use App\Services\CouponService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CouponController extends Controller
{
    public function __construct(private readonly CouponService $service) {}

    public function index(Request $request): View
    {
        $coupons = $this->service->list($request->only(['search', 'is_active']));
        return view('admin.coupons.index', compact('coupons'));
    }

    public function show(Coupon $coupon): View
    {
        return view('admin.coupons.show', compact('coupon'));
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(StoreCouponRequest $request): RedirectResponse
    {
        $coupon = $this->service->create($request->validated());

        return redirect()
            ->route('admin.coupons.show', $coupon)
            ->with('success', 'Coupon created.');
    }

    public function edit(Coupon $coupon): View
    {
        return view('admin.coupons.edit', compact('coupon'));
    }

    public function update(UpdateCouponRequest $request, Coupon $coupon): RedirectResponse
    {
        $this->service->update($coupon, $request->validated());

        return redirect()
            ->route('admin.coupons.show', $coupon)
            ->with('success', 'Coupon updated.');
    }

    public function destroy(Coupon $coupon): RedirectResponse
    {
        $this->service->delete($coupon);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Coupon deleted.');
    }
}