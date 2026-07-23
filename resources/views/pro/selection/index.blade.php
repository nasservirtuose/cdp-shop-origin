@extends('layouts.app')
@section('content')
<div class="container mx-auto px-4 py-8">
    <h1 class="text-3xl font-bold mb-6">Ma sélection</h1>

    <div class="flex gap-4 mb-6 border-b">
        <button class="tab-btn active px-4 py-2 border-b-2 border-blue-500" data-tab="selections">
            Ma sélection ({{ $selections->count() }})
        </button>
        <button class="tab-btn px-4 py-2 border-b-2 border-transparent hover:border-gray-300" data-tab="favorites">
            Mes favoris ({{ $favorites->count() }})
        </button>
        <button class="tab-btn px-4 py-2 border-b-2 border-transparent hover:border-gray-300" data-tab="packs">
            Mes packs ({{ $packs->count() }})
        </button>
    </div>

    <!-- TAB: Sélection -->
    <div id="tab-selections" class="tab-content">
        @if ($selections->isEmpty())
            <p class="text-gray-500">Aucun produit sélectionné. <a href="{{ route('pro.catalog.index') }}" class="text-blue-500 hover:underline">Parcourir le catalogue</a>.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($selections as $selection)
                    @if ($selection->product)
                        <div class="border rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition">
                            <h3 class="font-bold text-lg mb-2">{{ $selection->product->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ $selection->product->short_description }}</p>
                            <div class="flex gap-2">
                                <a href="{{ route('pro.catalog.show', $selection->product) }}" class="flex-1 text-center bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                                    Voir
                                </a>
                                <form action="{{ route('pro.selection.remove') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $selection->product_id }}">
                                    <button type="submit" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600">
                                        Retirer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <!-- TAB: Favoris -->
    <div id="tab-favorites" class="tab-content hidden">
        @if ($favorites->isEmpty())
            <p class="text-gray-500">Aucun favori. Marquez des produits en parcourant le catalogue.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach ($favorites as $favorite)
                    @if ($favorite->product)
                        <div class="border rounded-lg p-4 bg-yellow-50 shadow-sm hover:shadow-md transition">
                            <h3 class="font-bold text-lg mb-2">⭐ {{ $favorite->product->name }}</h3>
                            <p class="text-gray-600 text-sm mb-4">{{ $favorite->product->short_description }}</p>
                            <div class="flex gap-2">
                                <a href="{{ route('pro.catalog.show', $favorite->product) }}" class="flex-1 text-center bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                                    Voir
                                </a>
                                <form action="{{ route('pro.favorite.remove') }}" method="POST" class="flex-1">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $favorite->product_id }}">
                                    <button type="submit" class="w-full bg-red-500 text-white py-2 rounded hover:bg-red-600">
                                        Retirer
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>

    <!-- TAB: Packs -->
    <div id="tab-packs" class="tab-content hidden">
        @if ($packs->isEmpty())
            <p class="text-gray-500">Aucun pack créé. Créez un pack pour regrouper plusieurs produits.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($packs as $pack)
                    <div class="border rounded-lg p-4 bg-white shadow-sm hover:shadow-md transition">
                        <h3 class="font-bold text-lg mb-1">📦 {{ $pack->name }}</h3>
                        <p class="text-gray-600 text-sm mb-3">{{ $pack->description ?? '(sans description)' }}</p>
                        <p class="text-sm font-semibold mb-3">
                            <strong>{{ $pack->items->count() }}</strong> produit(s) | Statut: <span class="badge">{{ $pack->status }}</span>
                        </p>
                        <ul class="text-sm text-gray-700 mb-4">
                            @foreach ($pack->items as $item)
                                <li class="mb-1">• {{ $item->product->name }} (×{{ $item->quantity }})</li>
                            @endforeach
                        </ul>
                        <a href="#" class="text-blue-500 hover:underline text-sm">Éditer pack</a>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('pro.catalog.index') }}" class="bg-green-500 text-white px-6 py-3 rounded hover:bg-green-600">
            ➕ Parcourir le catalogue
        </a>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.dataset.tab;
            document.querySelectorAll('.tab-content').forEach(tc => tc.classList.add('hidden'));
            document.getElementById('tab-' + tabName).classList.remove('hidden');
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-blue-500');
                b.classList.add('border-transparent');
            });
            this.classList.add('border-blue-500');
            this.classList.remove('border-transparent');
        });
    });
</script>
@endsection
