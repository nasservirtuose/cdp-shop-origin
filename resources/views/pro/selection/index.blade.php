@extends('layouts.app')@section('title','Ma sélection')@section('content')
<div class="page">
    <div class="eyebrow">Espace pro</div>
    <div class="h1">Ma sélection</div>
    @if (session('success'))<div class="flash flash-ok" style="margin-top:20px">{{ session('success') }}</div>@endif    @if (session('error'))<div class="flash flash-err" style="margin-top:20px">{{ session('error') }}</div>@endif
    <div class="tabs">
        <button class="tab active" data-tab="sel">Ma sélection <span class="n">{{ $selections->count() }}</span></button>
        <button class="tab" data-tab="fav">Mes favoris <span class="n">{{ $favorites->count() }}</span></button>
        <button class="tab" data-tab="pk">Mes packs <span class="n">{{ $packs->count() }}</span></button>
    </div>
    <div id="tab-sel" class="tabc">
        @if ($selections->isEmpty())
            <p class="empty">Aucun produit sélectionné. <a href="{{ route('pro.catalog.index') }}">Parcourir le catalogue</a>.</p>
        @else
            <div class="grid">
                @foreach ($selections as $s) @if ($s->product)
                    <article class="card">
                        <div class="thumb"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 8h14l-1 11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></div>
                        <div class="cbody">
                            <div class="ctitle">{{ $s->product->name }}</div>
                            <p class="desc">{{ $s->product->short_description }}</p>
                            <div class="actions">
                                <a href="{{ route('pro.catalog.show', $s->product) }}" class="btn btn-ghost grow">Voir</a>
                                <form action="{{ route('pro.selection.remove') }}" method="POST">@csrf<input type="hidden" name="product_id" value="{{ $s->product_id }}"><button class="btn btn-ghost">Retirer</button></form>
                            </div>
                        </div>
                    </article>
                @endif @endforeach
            </div>
        @endif
    </div>
    <div id="tab-fav" class="tabc" style="display:none">
        @if ($favorites->isEmpty())
            <p class="empty">Aucun favori pour le moment.</p>
        @else
            <div class="grid">
                @foreach ($favorites as $f) @if ($f->product)
                    <article class="card">
                        <div class="thumb"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 8h14l-1 11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg></div>
                        <div class="cbody">
                            <div class="ctitle">{{ $f->product->name }}</div>
                            <p class="desc">{{ $f->product->short_description }}</p>
                            <div class="actions">
                                <a href="{{ route('pro.catalog.show', $f->product) }}" class="btn btn-ghost grow">Voir</a>
                                <form action="{{ route('pro.favorite.remove') }}" method="POST">@csrf<input type="hidden" name="product_id" value="{{ $f->product_id }}"><button class="btn btn-ghost">Retirer</button></form>
                            </div>
                        </div>
                    </article>
                @endif @endforeach
            </div>
        @endif
    </div>
    <div id="tab-pk" class="tabc" style="display:none">
        <div class="panel" style="margin-bottom:22px">
            <div style="font-weight:700;margin-bottom:12px">Créer un nouveau pack</div>
            <form action="{{ route('pro.packs.store') }}" method="POST" class="form-row">
                @csrf
                <input class="field" type="text" name="name" placeholder="Nom du pack" required>
                <input class="field" type="text" name="description" placeholder="Description (optionnel)">
                <button class="btn btn-primary">Créer</button>
            </form>
        </div>
        @if ($packs->isEmpty())
            <p class="empty">Aucun pack créé.</p>
        @else
            <div class="grid">
                @foreach ($packs as $pack)
                    <article class="card"><div class="cbody">
                        <span class="pill">{{ $pack->status->value }}</span>
                        <div class="ctitle" style="margin-top:10px">{{ $pack->name }}</div>
                        <p class="desc">{{ $pack->description ?? '—' }}</p>
                        <p class="desc" style="margin-top:8px">{{ $pack->items->count() }} produit{{ $pack->items->count() > 1 ? 's' : '' }}</p>
                        <div class="actions"><a href="{{ route('pro.packs.show', $pack) }}" class="btn btn-primary btn-block">Gérer ce pack</a></div>
                    </div></article>
                @endforeach
            </div>
        @endif
    </div>
</div>
<script>document.querySelectorAll('.tab').forEach(t=>t.addEventListener('click',function(){    document.querySelectorAll('.tab').forEach(x=>x.classList.remove('active'));    this.classList.add('active');    document.querySelectorAll('.tabc').forEach(c=>c.style.display='none');    document.getElementById('tab-'+this.dataset.tab).style.display='block';}));</script>
@endsection
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
        <div class="bg-white border rounded-lg p-4 mb-6">
            <h3 class="font-bold mb-3">Créer un nouveau pack</h3>
            <form action="{{ route('pro.packs.store') }}" method="POST" class="flex flex-col md:flex-row gap-3">
                @csrf
                <input type="text" name="name" placeholder="Nom du pack" required class="flex-1 border rounded px-3 py-2">
                <input type="text" name="description" placeholder="Description (optionnel)" class="flex-1 border rounded px-3 py-2">
                <button type="submit" class="bg-green-500 text-white px-6 py-2 rounded hover:bg-green-600">Créer</button>
            </form>
        </div>

        @if ($packs->isEmpty())
            <p class="text-gray-500">Aucun pack créé.</p>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @foreach ($packs as $pack)
                    <div class="border rounded-lg p-4 bg-white shadow-sm">
                        <h3 class="font-bold text-lg mb-1">📦 {{ $pack->name }}</h3>
                        <p class="text-gray-600 text-sm mb-2">{{ $pack->description ?? '(sans description)' }}</p>
                        <p class="text-sm mb-3"><strong>{{ $pack->items->count() }}</strong> produit(s) · {{ $pack->status->value }}</p>
                        <ul class="text-sm text-gray-700 mb-4">
                            @foreach ($pack->items as $item)
                                <li>• {{ $item->product->name ?? '?' }} (×{{ $item->quantity }})</li>
                            @endforeach
                        </ul>
                        <a href="{{ route('pro.packs.show', $pack) }}" class="text-blue-500 hover:underline text-sm">Gérer ce pack →</a>
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
