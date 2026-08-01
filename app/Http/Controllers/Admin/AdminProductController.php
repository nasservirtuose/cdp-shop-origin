<?php

namespace App\Http\Controllers\Admin;

use App\Enums\CommerceMode;
use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use App\Models\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $tiers = $this->validatedTiers($request);
        $product = ShopProduct::create($this->validatedData($request));
        $this->syncTiers($product, $tiers);

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
        $tiers = $this->validatedTiers($request);
        $product->update($this->validatedData($request, $product->id));
        $this->syncTiers($product, $tiers);

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
            'external_reference' => [
                'required',
                'string',
                'max:100',
                Rule::unique('shop_products', 'external_reference')->ignore($ignoreId),
            ],
            'category_id' => 'nullable|integer|exists:shop_categories,id',
            'short_description' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'commerce_mode' => 'required|in:DIRECT_SHOP,EXTERNAL_AFFILIATE,PARTNER',
            'external_checkout_url' => 'nullable|url|max:2000',
            'main_image' => 'nullable|url|max:2000',
            'price_ttc' => 'nullable|numeric|min:0',
            'vat_percent' => 'nullable|numeric|min:0|max:100',
            'purchase_cost_ht' => 'nullable|numeric|min:0',
            'variable_costs_ht' => 'nullable|numeric|min:0',
            'rex_share_percent' => 'nullable|numeric|min:0|max:100',
            'low_bound' => 'nullable|numeric|min:0',
            'high_bound' => 'nullable|numeric|min:0',
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
            'external_reference' => $data['external_reference'],
            'category_id' => $data['category_id'] ?? null,
            'short_description' => $data['short_description'] ?? null,
            'description' => $data['description'] ?? null,
            'commerce_mode' => $data['commerce_mode'],
            'external_checkout_url' => $data['external_checkout_url'] ?? null,
            'main_image' => $data['main_image'] ?? null,
            'price_ttc' => $data['price_ttc'] ?? null,
            'vat_percent' => $data['vat_percent'] ?? null,
            'purchase_cost_ht' => $data['purchase_cost_ht'] ?? null,
            'variable_costs_ht' => $data['variable_costs_ht'] ?? null,
            'rex_share_percent' => $data['rex_share_percent'] ?? null,
            'low_bound' => $data['low_bound'] ?? null,
            'high_bound' => $data['high_bound'] ?? null,
            'is_active' => $request->boolean('is_active'),
            'is_public' => $request->boolean('is_public'),
        ];
    }

    private function validatedTiers(Request $request): array
    {
        $validated = $request->validate([
            'tiers' => 'required|array|size:3',
            'tiers.*.range_start_percentage' => 'required|numeric|min:0|max:100',
            'tiers.*.range_end_percentage' => 'required|numeric|min:0|max:100',
            'tiers.*.probability_percentage' => 'required|integer|min:0|max:100',
        ]);

        $sum = collect($validated['tiers'])->sum(fn ($t) => (int) $t['probability_percentage']);
        if ($sum !== 100) {
            throw ValidationException::withMessages([
                'tiers' => "La somme des probabilites des 3 tranches doit faire 100 % (actuel : {$sum} %).",
            ]);
        }

        return $validated['tiers'];
    }

    private function syncTiers(ShopProduct $product, array $tiers): void
    {
        foreach ($tiers as $tierNumber => $t) {
            $product->rewardTiers()->updateOrCreate(
                ['tier_number' => (int) $tierNumber],
                [
                    'range_start_percentage' => $t['range_start_percentage'],
                    'range_end_percentage' => $t['range_end_percentage'],
                    'probability_percentage' => (int) $t['probability_percentage'],
                    'is_active' => true,
                ]
            );
        }
    }
}
