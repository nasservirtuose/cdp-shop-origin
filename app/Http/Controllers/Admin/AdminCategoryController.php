<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function index()
    {
        $categories = ShopCategory::withCount('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.categories.index', ['categories' => $categories]);
    }

    public function create()
    {
        return view('admin.categories.form', [
            'category' => new ShopCategory(),
            'categories' => ShopCategory::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        ShopCategory::create($this->validatedData($request));

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie créée.');
    }

    public function edit(ShopCategory $category)
    {
        return view('admin.categories.form', [
            'category' => $category,
            'categories' => ShopCategory::where('id', '!=', $category->id)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, ShopCategory $category)
    {
        $category->update($this->validatedData($request, $category->id));

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie mise à jour.');
    }

    public function destroy(ShopCategory $category)
    {
        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Catégorie supprimée.');
    }

    private function validatedData(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255',
            'parent_id' => 'nullable|integer|exists:shop_categories,id',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $base = Str::slug($data['slug'] ?: $data['name']);
        $slug = $base;
        $i = 2;

        while (ShopCategory::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $i++;
        }

        return [
            'name' => $data['name'],
            'slug' => $slug,
            'parent_id' => $data['parent_id'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
