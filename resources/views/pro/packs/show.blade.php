@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <a href="{{ route('pro.selection.index') }}" class="text-blue-500 hover:underline mb-6 inline-block">← Retour à Ma sélection</a>

    @if (session('success')) <div class="bg-green-100 text-green-800 p-3 rounded mb-4">{{ session('success') }}</div> @endif
    @if (session('error')) <div class="bg-red-100 text-red-800 p-3 rounded mb-4">{{ session('error') }}</div> @endif

    <div class="flex justify-between items-start mb-6">
        <div>
            <h1 class="text-3xl font-bold">📦 {{ $pack->name }}</h1>
            <p class="text-gray-600">{{ $pack->description ?? '(sans description)' }}</p>
        </div>
        <form action="{{ route('pro.packs.destroy', $pack) }}" method="POST" onsubmit="return confirm('Supprimer ce pack ?')">
            @csrf @method('DELETE')
            <button class="text-red-500 text-sm hover:underline">Supprimer ce pack</button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Produits DANS le pack -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Produits du pack ({{ $pack->items->count() }})</h2>
            @if ($pack->items->isEmpty())
                <p class="text-gray-500">Ce pack est vide. Ajoute des produits depuis ta sélection →</p>
            @else
                <div class="space-y-3">
                    @foreach ($pack->items as $item)
                        <div class="border rounded p-3 bg-white flex justify-between items-center">
                            <span>{{ $item->product->name ?? '?' }} <span class="text-gray-400 text-sm">×{{ $item->quantity }}</span></span>
                            <form action="{{ route('pro.packs.items.remove', $pack) }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->product_id }}">
                                <button class="text-red-500 hover:underline text-sm">Retirer</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <!-- Produits de Ma sélection à ajouter -->
        <div>
            <h2 class="text-xl font-semibold mb-4">Ajouter depuis Ma sélection</h2>
            @if ($selections->isEmpty())
                <p class="text-gray-500">Ta sélection est vide. <a href="{{ route('pro.catalog.index') }}" class="text-blue-500 hover:underline">Parcourir le catalogue</a>.</p>
            @else
                <div class="space-y-3">
                    @foreach ($selections as $selection)
                        @if ($selection->product)
                            <div class="border rounded p-3 bg-gray-50 flex justify-between items-center">
                                <span>{{ $selection->product->name }}</span>
                                <form action="{{ route('pro.packs.items.add', $pack) }}" method="POST" class="flex gap-2 items-center">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $selection->product_id }}">
                                    <input type="number" name="quantity" value="1" min="1" class="w-16 border rounded px-2 py-1 text-sm">
                                    <button class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">+ Pack</button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
