<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductService $service) {}

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/products
    |
    | Params (all optional, combinable):
    |   ?search=polo              — filter by name
    |   ?type=man                 — man | women | kids | unisex
    |
    | Lean card payload per product:
    |   id, name
    |   colors  — [{ name, hex }]
    |   images  — [ url, url ]  (exactly 2 primary images)
    |   price   — { actual, current, currency }
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type'   => ['nullable', 'in:man,women,kids,unisex'],
        ]);

        $paginator = $this->service->listForApi($request->only(['search', 'type']));

        $items = collect($paginator->items())->map(function ($p) {
            $variant = $p->variants->first();
            $price   = $variant?->prices->first();

            return [
                'id'     => $p->id,
                'name'   => $p->name,
                'colors' => $p->colors->map(fn ($c) => [
                    'name' => $c->color_name,
                ]),
                'images' => $p->images->map(fn ($img) => $img->url)->values(),
                'price'  => $price ? [
                    'actual'   => $price->actual_price,
                    'discount_type' => $price->discount_type,
                    'discount' => $price->discount_value,
                    'current'  => $price->current_price,
                    'currency' => $price->currency?->symbol,
                ] : null,
            ];
        });

        return response()->json([
            'success' => true,
            // 'filters' => [
            //     'search' => $request->search,
            //     'type'   => $request->type,
            // ],
            // 'meta' => [
            //     'total'        => $paginator->total(),
            //     'per_page'     => $paginator->perPage(),
            //     'current_page' => $paginator->currentPage(),
            //     'last_page'    => $paginator->lastPage(),
            // ],
            'data' => $items,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/products/{id}
    |
    | Full product detail:
    |   colors, sizes, all images (with variant_id for color grouping),
    |   all active variants (color + size labels)
    |     — all prices per variant with currency info
    |--------------------------------------------------------------------------
    */
    public function show(string $id): JsonResponse
    {
        $product = $this->service->findActiveOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => [
                'id'           => $product->id,
                'product_code' => $product->product_code,
                'name'         => $product->name,
                'type'         => $product->type,
                'description'  => $product->description,
                'colors'       => $product->colors->map(fn ($c) => [
                    'name' => $c->color_name,
                    'hex'  => $c->color_hex,
                ]),
                'sizes'        => $product->sizes->map(fn ($s) => [
                    'label' => $s->size_label,
                ]),
                'variants'     => $product->variants->map(function ($v) {
                    $price = $v->prices->firstWhere('is_default', true) ?? $v->prices->first();
                    return [
                        'id'     => $v->id,
                        'color'  => $v->color ? [
                            'name' => $v->color->color_name,
                            'hex'  => $v->color->color_hex,
                        ] : null,
                        'size'   => $v->size ? [
                            'label' => $v->size->size_label,
                        ] : null,
                        'images' => $v->images->map(fn ($img) => $img->url)->values(),
                        'price'  => $price ? [
                            'actual'        => $price->actual_price,
                            'discount_type' => $price->discount_type,
                            'discount'      => $price->discount_value,
                            'current'       => $price->current_price,
                            'currency'      => $price->currency?->symbol,
                        ] : null,
                    ];
                }),
            ],
        ]);
    }
}
