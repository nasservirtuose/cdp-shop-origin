@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-2">Catalogue des produits</h1>
    <p class="text-gray-600 mb-8">Parcourez et sélectionnez les produits à recommander à vos clients.</p>

    @foreach ($categories as $category)
        @if ($category->products->count() > 0)
            <div class="mb-12">
                <h2 class="text-2xl font-semibold mb-6 text-gray-800">{{ $category->name }}</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach ($category->products as $product)
                        <div class="border rounded-lg overflow-hidden bg-white shadow-sm hover:shadow-lg transition">
                            @if ($product->main_image)
                                <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="w-full h-48 object-cover bg-gray-200">
                            @else
                                <div class="w-full h-48 bg-gray-200 flex items-center justify-center text-gray-400">
                                    Pas d'image
                                </div>
                            @endif

                            <div class="p-4">
                                <h3 class="font-bold text-lg mb-2 line-clamp-2">{{ $product->name }}</h3>
                                <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $product->short_description }}</p>

                                <!-- Emplacement fourchette Rex (vide en M1) -->
                                <div class="bg-gray-50 p-2 rounded mb-4 text-center text-gray-400 text-xs h-6 flex items-center justify-center">
                                    Fourchette récompense (M3)
                                </div>

                                <div class="flex gap-2 mb-3">
                                    <a href="{{ route('pro.catalog.show', $product) }}" class="flex-1 text-center bg-blue-500 text-white py-2 rounded text-sm hover:bg-blue-600">
                                        Voir
                                    </a>
                                </div>

                                <div class="flex gap-2">
                                    <form action="{{ route('pro.selection.add') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full bg-green-500 text-white py-2 rounded text-sm hover:bg-green-600
                                            {{ in_array($product->id, $selectedIds) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ in_array($product->id, $selectedIds) ? 'disabled' : '' }}>
                                            {{ in_array($product->id, $selectedIds) ? '✓ Sélectionné' : 'Ajouter' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('pro.favorite.add') }}" method="POST" class="flex-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="w-full bg-yellow-500 text-white py-2 rounded text-sm hover:bg-yellow-600
                                            {{ in_array($product->id, $favoriteIds) ? 'opacity-50 cursor-not-allowed' : '' }}"
                                            {{ in_array($product->id, $favoriteIds) ? 'disabled' : '' }}>
                                            {{ in_array($product->id, $favoriteIds) ? '⭐ Favori' : '☆ Favori' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    @endforeach

    <div class="mt-8">
        <a href="{{ route('pro.selection.index') }}" class="bg-blue-500 text-white px-6 py-3 rounded hover:bg-blue-600">
            ← Retour à Ma sélection
        </a>
    </div>
</div>
@endsection
