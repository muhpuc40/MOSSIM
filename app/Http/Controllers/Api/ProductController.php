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
    |   (none)                    → all active products
    |   ?search=polo              → filter by name
    |   ?type=man                 → filter by type
    |   ?type=women&search=dress  → combined
    |
    | Each product includes:
    |   colors, sizes, primary image,
    |   default variant → default price (current_price, currency)
    |--------------------------------------------------------------------------
    */
    public function index(Request $request): JsonResponse
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'type'   => ['nullable', 'in:man,women,kids,unisex'],
        ]);

        $products = $this->service->listForApi(
            $request->only(['search', 'type'])
        );

        return response()->json([
            'success' => true,
            'filters' => [
                'search' => $request->search,
                'type'   => $request->type,
            ],
            'data' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | GET /api/v1/products/{id}
    |
    | Full product detail:
    |   colors, sizes, all images,
    |   all active variants → all prices per variant with currency info
    |--------------------------------------------------------------------------
    */
    public function show(string $id): JsonResponse
    {
        $product = $this->service->findActiveOrFail($id);

        return response()->json([
            'success' => true,
            'data'    => $product,
        ]);
    }
}