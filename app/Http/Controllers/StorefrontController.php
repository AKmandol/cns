<?php

namespace App\Http\Controllers;

use App\Models\Shop;
use Illuminate\Http\JsonResponse;

class StorefrontController extends Controller
{
    public function index(): JsonResponse
    {
        $shops = Shop::query()
            ->where('is_active', true)
            ->withCount(['listings as active_listings_count' => fn ($query) => $query->where('is_active', true)])
            ->orderBy('name')
            ->get();

        return response()->json(['shops' => $shops]);
    }

    public function show(Shop $shop): JsonResponse
    {
        abort_unless($shop->is_active, 404);

        $products = $shop->listings()
            ->with('product')
            ->where('is_active', true)
            ->whereHas('product')
            ->latest()
            ->get();

        return response()->json(compact('shop', 'products'));
    }
}
