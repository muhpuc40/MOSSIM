<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Price\StorePriceRequest;
use App\Models\ProductPrice;
use App\Models\ProductVariant;
use App\Services\PricingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PriceController extends Controller
{
    public function __construct(private readonly PricingService $service) {}

    // GET /admin/variants/{variant}/prices
    public function index(ProductVariant $variant): View
    {
        $prices = $this->service->pricesForVariant($variant->id);

        return view('admin.prices.index', compact('variant', 'prices'));
    }

    // GET /admin/variants/{variant}/prices/create
    public function create(ProductVariant $variant): View
    {
        $currencies = $this->service->allCurrencies();

        return view('admin.prices.create', compact('variant', 'currencies'));
    }

    // POST /admin/variants/{variant}/prices
    public function store(StorePriceRequest $request, ProductVariant $variant): RedirectResponse
    {
        $this->service->setPrice($variant, $request->validated());

        return redirect()
            ->route('admin.variants.prices.index', $variant)
            ->with('success', 'Price saved.');
    }

    // DELETE /admin/prices/{price}
    public function destroy(ProductPrice $price): RedirectResponse
    {
        $variantId = $price->variant_id;
        $this->service->delete($price);

        return redirect()
            ->route('admin.variants.prices.index', $variantId)
            ->with('success', 'Price removed.');
    }
}