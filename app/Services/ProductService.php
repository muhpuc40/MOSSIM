<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProductService
{
    /*
    |--------------------------------------------------------------------------
    | SEARCH PARAMS (all optional, combinable)
    |
    |   ?search=polo              → name LIKE %polo%
    |   ?type=man                 → enum filter
    |   ?type=women&search=dress  → combined
    |   (none)                    → all
    |
    | Price loading strategy:
    |   - API  → loads default price of default variant in default currency
    |   - Show → loads all prices for all variants with currency info
    |--------------------------------------------------------------------------
    */

    // ─── Admin: list all with filters ────────────────────────────────────────

    public function listForAdmin(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('name', 'like', "%{$v}%")
                  ->orWhere('product_code', 'like', "%{$v}%")
            )
            ->when($filters['type'] ?? null, fn ($q, $v) =>
                $q->where('type', $v)
            )
            ->when(isset($filters['is_active']), fn ($q) =>
                $q->where('is_active', (bool) $filters['is_active'])
            )
            ->with([
                'colors',
                'sizes',
                'images'   => fn ($q) => $q->where('is_primary', true)->orderBy('sort_order'),
                'variants' => fn ($q) => $q->where('is_active', true),
                'variants.prices' => fn ($q) => $q->where('is_default', true)->with('currency'),
            ])
            ->latest()
            ->paginate(20);
    }

    // ─── API: list active products — default price included ──────────────────

    public function listForApi(array $filters = []): LengthAwarePaginator
    {
        return Product::query()
            ->where('is_active', true)
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('name', 'like', "%{$v}%")
            )
            ->when($filters['type'] ?? null, fn ($q, $v) =>
                $q->where('type', $v)
            )
            ->with([
                'colors',
                'sizes',
                'images'                     => fn ($q) => $q->where('is_primary', true)->orderBy('sort_order'),
                'variants'                   => fn ($q) => $q->where('is_active', true)->where('is_default', true),
                'variants.prices'            => fn ($q) => $q->where('is_default', true)->with('currency'),
            ])
            ->latest()
            ->paginate(20);
    }

    // ─── API: single active product — full detail with all prices ────────────

    public function findActiveOrFail(string $id): Product
    {
        return Product::where('id', $id)
            ->where('is_active', true)
            ->with([
                'colors',
                'sizes',
                'images'              => fn ($q) => $q->orderBy('sort_order'),
                'variants'            => fn ($q) => $q->where('is_active', true)->with(['color', 'size', 'images']),
                'variants.prices'     => fn ($q) => $q->with('currency'),
            ])
            ->firstOrFail();
    }

    // ─── Admin: single product — full detail ─────────────────────────────────

    public function findOrFail(string $id): Product
    {
        return Product::with([
            'colors',
            'sizes',
            'images',
            'variants.color',
            'variants.size',
            'variants.prices.currency',
        ])->findOrFail($id);
    }

    // ─── Admin: create product ────────────────────────────────────────────────

    public function create(array $data): Product
    {
        $product = Product::create([
            'product_code' => $data['product_code'],
            'name'         => $data['name'],
            'type'         => $data['type'],
            'description'  => $data['description'],
            'is_active'    => $data['is_active'] ?? true,
            'created_by'   => Auth::id(),
        ]);

        if (!empty($data['colors'])) {
            $product->colors()->createMany($data['colors']);
        }

        if (!empty($data['sizes'])) {
            $product->sizes()->createMany($data['sizes']);
        }

        return $product;
    }

    // ─── Admin: update ────────────────────────────────────────────────────────

    public function update(Product $product, array $data): Product
    {
        $product->update($data);
        return $product->fresh();
    }

    // ─── Admin: deactivate (safe — no hard delete) ───────────────────────────

    public function delete(Product $product): void
    {
        $product->update(['is_active' => false]);
    }
}