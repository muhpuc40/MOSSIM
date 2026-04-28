<?php

namespace App\Services;

use App\Models\Currency;
use App\Models\ProductPrice;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class PricingService
{
    // ─── Set price for a variant in a currency ────────────────────────────────

    public function setPrice(ProductVariant $variant, array $data): ProductPrice
    {
        return DB::transaction(function () use ($variant, $data) {

            // If this is marked default, clear existing default for same variant+currency
            if (!empty($data['is_default'])) {
                ProductPrice::where('variant_id', $variant->id)
                    ->where('currency_id', $data['currency_id'])
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }

            return ProductPrice::updateOrCreate(
                [
                    'variant_id'  => $variant->id,
                    'currency_id' => $data['currency_id'],
                ],
                [
                    'actual_price'   => $data['actual_price'],
                    'discount_type'  => $data['discount_type'] ?? null,
                    'discount_value' => $data['discount_value'] ?? 0,
                    'is_default'     => $data['is_default'] ?? false,
                ]
            );
        });
    }

    // ─── Get all prices for a variant ────────────────────────────────────────

    public function pricesForVariant(string $variantId)
    {
        return ProductPrice::where('variant_id', $variantId)
            ->with('currency')
            ->get();
    }

    // ─── Delete a price row ───────────────────────────────────────────────────

    public function delete(ProductPrice $price): void
    {
        $price->delete();
    }

    // ─── Get default currency ─────────────────────────────────────────────────

    public function defaultCurrency(): ?Currency
    {
        return Currency::where('is_default', true)->first();
    }

    // ─── All currencies ───────────────────────────────────────────────────────

    public function allCurrencies()
    {
        return Currency::orderBy('code')->get();
    }
}