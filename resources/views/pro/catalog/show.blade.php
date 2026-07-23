@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <a href="{{ route('pro.catalog.index') }}" class="text-blue-500 hover:underline mb-6 inline-block">← Retour au catalogue</a>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Image -->
        <div>
            @if ($product->main_image)
                <img src="{{ asset('storage/' . $product->main_image) }}" alt="{{ $product->name }}" class="w-full rounded-lg shadow-lg">
            @else
                <div class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center text-gray-400">
                    Pas d'image disponible
                </div>
            @endif
        </div>

        <!-- Infos produit -->
        <div>
            <h1 class="text-4xl font-bold mb-4">{{ $product->name }}</h1>
            <p class="text-gray-600 text-lg mb-4">{{ $product->short_description }}</p>

            @if ($product->category)
                <p class="text-sm text-gray-500 mb-6">
                    <strong>Catégorie :</strong> {{ $product->category->name }}
                </p>
            @endif

            <!-- Description complète -->
            <div class="bg-gray-50 p-6 rounded-lg mb-6">
                <h2 class="font-bold text-lg mb-2">Description</h2>
                <p class="text-gray-700 leading-relaxed">
                    {{ $product->description ?? '(pas de description disponible)' }}
                </p>
            </div>

            <!-- Emplacement fourchette Rex (vide en M1) -->
            <div class="bg-blue-50 p-6 rounded-lg mb-6 text-center">
                <p class="text-gray-500 text-sm">Fourchette de récompense Rex</p>
                <p class="text-gray-400 text-xs mt-2">(Disponible en M3)</p>
            </div>

            <!-- Actions -->
            <div class="flex gap-3">
                @if ($isSelected)
                    <form action="{{ route('pro.selection.remove') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="w-full bg-red-500 text-white py-3 rounded-lg font-bold hover:bg-red-600">
                            ✓ Retirer de ma sélection
                        </button>
                    </form>
                @else
                    <form action="{{ route('pro.selection.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="w-full bg-green-500 text-white py-3 rounded-lg font-bold hover:bg-green-600">
                            ➕ Ajouter à ma sélection
                        </button>
                    </form>
                @endif

                @if ($isFavorite)
                    <form action="{{ route('pro.favorite.remove') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="w-full bg-yellow-500 text-white py-3 rounded-lg font-bold hover:bg-yellow-600">
                            ⭐ Retirer des favoris
                        </button>
                    </form>
                @else
                    <form action="{{ route('pro.favorite.add') }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="w-full bg-yellow-500 text-white py-3 rounded-lg font-bold hover:bg-yellow-600">
                            ☆ Ajouter aux favoris
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
