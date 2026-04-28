<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Product\StoreProductRequest;
use App\Http\Requests\Admin\Product\UpdateProductRequest;
use App\Models\Product;
use App\Services\PricingService;
use App\Services\ProductService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $service,
        private readonly PricingService $pricing,
    ) {}

    // ─── index ───────────────────────────────────────────────────────────────
    // GET /admin/products
    // ?search=polo | ?type=man | ?is_active=0
    public function index(Request $request): View
    {
        $products = $this->service->listForAdmin(
            $request->only(['search', 'type', 'is_active'])
        );

        return view('admin.products.index', compact('products'));
    }

    // ─── show ─────────────────────────────────────────────────────────────────
    // GET /admin/products/{product}
    // Includes: colors, sizes, variants with prices + currency
    public function show(Product $product): View
    {
        $product = $this->service->findOrFail($product->id);

        return view('admin.products.show', compact('product'));
    }

    // ─── create ───────────────────────────────────────────────────────────────
    public function create(): View
    {
        $currencies = $this->pricing->allCurrencies();

        return view('admin.products.create', compact('currencies'));
    }

    // ─── store ────────────────────────────────────────────────────────────────
    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = $this->service->create($request->validated());

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product created.');
    }

    // ─── edit ─────────────────────────────────────────────────────────────────
    public function edit(Product $product): View
    {
        $product->load(['colors', 'sizes', 'variants.prices.currency']);
        $currencies = $this->pricing->allCurrencies();

        return view('admin.products.edit', compact('product', 'currencies'));
    }

    // ─── update ───────────────────────────────────────────────────────────────
    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $this->service->update($product, $request->validated());

        return redirect()
            ->route('admin.products.show', $product)
            ->with('success', 'Product updated.');
    }

    // ─── destroy ──────────────────────────────────────────────────────────────
    public function destroy(Product $product): RedirectResponse
    {
        $this->service->delete($product);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deactivated.');
    }
}