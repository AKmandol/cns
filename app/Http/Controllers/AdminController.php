<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Shop;
use App\Models\ShopProduct;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminController extends Controller
{
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'shopCount' => Shop::count(),
            'productCount' => Product::count(),
            'ownerCount' => User::where('role', 'owner')->count(),
            'listingCount' => ShopProduct::count(),
            'recentShops' => Shop::with('owner')->latest()->take(5)->get(),
            'recentProducts' => Product::with('creator')->latest()->take(5)->get(),
        ]);
    }

    public function shops(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $shops = Shop::with('owner')->withCount('listings')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhereHas('owner', fn ($ownerQuery) => $ownerQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"));
            }))
            ->latest()->paginate(10)->withQueryString();

        return response()->json([
            'shops' => $shops,
            'owners' => User::where('role', 'owner')->orderBy('name')->get(['id', 'name', 'email']),
        ]);
    }

    public function storeShop(Request $request): JsonResponse
    {
        $shop = Shop::create($this->validateShop($request));

        return response()->json(['message' => 'Shop created.', 'shop' => $shop->load('owner')], 201);
    }

    public function updateShop(Request $request, Shop $shop): JsonResponse
    {
        $shop->update($this->validateShop($request, $shop));

        return response()->json(['message' => 'Shop updated.', 'shop' => $shop->fresh('owner')]);
    }

    public function destroyShop(Shop $shop): JsonResponse
    {
        $shop->delete();

        return response()->json(['message' => 'Shop deleted.']);
    }

    public function products(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $products = Product::with('creator')->withCount('listings')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhereHas('creator', fn ($creatorQuery) => $creatorQuery->where('name', 'like', "%{$search}%"));
            }))
            ->latest()->paginate(10)->withQueryString();

        return response()->json(['products' => $products]);
    }

    public function storeProduct(Request $request): JsonResponse
    {
        $product = $request->user()->createdProducts()->create($this->validateProduct($request));

        return response()->json(['message' => 'Catalog product created.', 'product' => $product->load('creator')], 201);
    }

    public function updateProduct(Request $request, Product $product): JsonResponse
    {
        $product->update($this->validateProduct($request, $product));

        return response()->json(['message' => 'Catalog product updated.', 'product' => $product->fresh('creator')]);
    }

    public function destroyProduct(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(['message' => 'Catalog product deleted.']);
    }

    public function owners(Request $request): JsonResponse
    {
        $search = $request->string('search')->trim()->toString();
        $owners = User::where('role', 'owner')->withCount('shops')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->latest()->paginate(10)->withQueryString();

        return response()->json(['owners' => $owners]);
    }

    public function storeOwner(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $owner = User::create([...$data, 'role' => 'owner']);

        return response()->json(['message' => 'Shop owner account created.', 'owner' => $owner], 201);
    }

    private function validateShop(Request $request, ?Shop $shop = null): array
    {
        return $request->validate([
            'owner_id' => ['required', Rule::exists('users', 'id')->where('role', 'owner')],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('shops', 'slug')->ignore($shop)],
            'description' => ['nullable', 'string'],
            'address' => ['nullable', 'string', 'max:255'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function validateProduct(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255', Rule::unique('products', 'sku')->ignore($product)],
            'description' => ['nullable', 'string'],
        ]);
    }
}
