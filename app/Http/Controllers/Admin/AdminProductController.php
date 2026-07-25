<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommerceMode;
use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminProductController extends Controller
{
    public function index()
    {
        return view('admin.products.index', [
            'products' => ShopProduct::with('category')->orderByDesc('id')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => new ShopProduct(),
            'categories' => ShopCategory::orderBy('name')->get(),
            'modes' => CommerceMode::cases(),
        ]);
    }

    public function store(Request $request)
    {
        ShopProduct::create($this->validatedData($request));

        return redirect()->route('admin.products.index')->with('success', 'Produit cree.');
    }

    public function edit(ShopProduct $product)
    {
        return view('admin.products.form', [
            'product' => $product,
            'categories' => ShopCategory::orderBy('name')->get(),
            'modes' => CommerceMode::cases(),
        ]);
    }

    public function update(Request $request, ShopProduct $product)
    {
        $product->update($this->validatedData($request, $product->id));

        return redirect()->route('admin.products.index')->with('success', 'Produit mis a jour.');
    }

    public function destroy(ShopProduct $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Produit supprime.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'category_id' => 'nullable|integer|exists:shop_categories,id',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'commerce_mode' => 'required|in:DIRECT_SHOP,EXTERNAL_AFFILIATE,PARTNER',
            'external_checkout_url' => 'nullable|url|max:2000',
            'main_image' => 'nullable|url|max:2000',
        ]);

        $base = Str::slug($data['slug'] ?: $data['name']);
        $slug = $base;
        $i = 2;

        while (ShopProduct::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return [
            'name' => $data['name'],
            'slug' => $slug,
            'category_id' => $data['category_id'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'commerce_mode' => $data['commerce_mode'],
            'external_checkout_url' => $data['external_checkout_url'] ?? null,
            'main_image' => $data['main_image'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'is_public' => $request->boolean('is_public'),
        ];
    }
}
