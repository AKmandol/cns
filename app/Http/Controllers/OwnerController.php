<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class OwnerController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $shops = $request->user()->shops()->withCount('listings')->orderBy('name')->get();

        return response()->json(['shops' => $shops]);
    }

    public function products(Request $request, Shop $shop): JsonResponse
    {
        $this->ensureShopOwner($request, $shop);

        $listings = $shop->listings()->with('product.creator')->latest()->get();
        $availableProducts = Product::query()
            ->whereDoesntHave('listings', fn ($query) => $query->where('shop_id', $shop->id))
            ->orderBy('name')
            ->get();

        return response()->json(compact('shop', 'listings', 'availableProducts'));
    }

    public function storeListing(Request $request, Shop $shop): JsonResponse
    {
        $this->ensureShopOwner($request, $shop);

        $data = $request->validate([
            'product_id' => [
                'required',
                'integer',
                Rule::exists('products', 'id'),
                Rule::unique('shop_products', 'product_id')->where('shop_id', $shop->id),
            ],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);

        $thumbnail = $request->file('thumbnail')?->store('shop-thumbnails', 'public');

        $shop->listings()->create([
            'product_id' => $data['product_id'],
            'price' => $data['price'],
            'description' => $data['description'] ?? null,
            'thumbnail' => $thumbnail,
            'is_active' => true,
        ]);

        return response()->json(['message' => 'Product added to your shop.'], 201);
    }

    public function storeNewProduct(Request $request, Shop $shop): JsonResponse
    {
        $this->ensureShopOwner($request, $shop);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', 'unique:products,sku'],
            'catalog_description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
        ]);

        $thumbnail = $request->file('thumbnail')?->store('shop-thumbnails', 'public');

        $listing = DB::transaction(function () use ($request, $shop, $data, $thumbnail): ShopProduct {
            $product = $request->user()->createdProducts()->create([
                'name' => $data['name'],
                'sku' => $data['sku'] ?? null,
                'description' => $data['catalog_description'] ?? null,
            ]);

            return $shop->listings()->create([
                'product_id' => $product->id,
                'price' => $data['price'],
                'description' => $data['description'] ?? null,
                'thumbnail' => $thumbnail,
                'is_active' => true,
            ]);
        });

        return response()->json(['message' => 'Product created and listed in your shop.', 'listing' => $listing->load('product.creator')], 201);
    }

    public function updateListing(Request $request, Shop $shop, ShopProduct $listing): JsonResponse
    {
        $this->ensureShopOwner($request, $shop);
        abort_unless($listing->shop_id === $shop->id, 404);

        $data = $request->validate([
            'price' => ['required', 'numeric', 'min:0', 'max:99999999.99'],
            'description' => ['nullable', 'string'],
            'thumbnail' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['required', 'boolean'],
        ]);

        $oldThumbnail = $listing->thumbnail;
        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('shop-thumbnails', 'public');
        }

        $listing->update($data);

        if ($request->hasFile('thumbnail') && $oldThumbnail) {
            Storage::disk('public')->delete($oldThumbnail);
        }

        return response()->json(['message' => 'Shop listing updated.', 'listing' => $listing->fresh('product.creator')]);
    }

    public function destroyListing(Request $request, Shop $shop, ShopProduct $listing): JsonResponse
    {
        $this->ensureShopOwner($request, $shop);
        abort_unless($listing->shop_id === $shop->id, 404);

        if ($listing->thumbnail) {
            Storage::disk('public')->delete($listing->thumbnail);
        }

        $listing->delete();

        return response()->json(['message' => 'Product removed from your shop.']);
    }

    private function ensureShopOwner(Request $request, Shop $shop): void
    {
        abort_unless($shop->owner_id === $request->user()->id, 403);
    }
}
