@extends('layouts.app')@section('title','Catalogue')@section('content')
<div class="page">
    <div class="eyebrow">Catalogue</div>
    <div class="h1">Les produits que vous recommandez</div>
    <p class="lede">Ajoutez des produits à votre sélection pour les partager à vos clients et composer des packs.</p>

    @foreach ($categories as $category)
        @if ($category->products->count() > 0)
            <div class="sec-head">
                <span class="h2">{{ $category->name }}</span>
                <span class="count">{{ $category->products->count() }} produit{{ $category->products->count() > 1 ? 's' : '' }}</span>
                <span class="rule"></span>
            </div>
            <div class="grid">
                @foreach ($category->products as $product)
                    @php $sel = in_array($product->id, $selectedIds); $fav = in_array($product->id, $favoriteIds); @endphp
                    <article class="card">
                        <div class="thumb">
                            <form action="{{ $fav ? route('pro.favorite.remove') : route('pro.favorite.add') }}" method="POST">
                                @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button class="fav {{ $fav ? 'on' : '' }}" aria-label="Favori"><svg viewBox="0 0 24 24" fill="{{ $fav ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.7"><path d="M12 21s-7.5-4.9-9.7-9.3C.9 8.5 2.3 5.5 5.3 5.5c1.9 0 3.1 1 3.9 2.2h.1c.8-1.2 2-2.2 3.9-2.2 3 0 4.4 3 3 6.2C19.5 16.1 12 21 12 21z"/></svg></button>
                            </form>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M5 8h14l-1 11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
                        </div>
                        <div class="cbody">
                            <span class="chip">{{ $category->name }}</span>
                            <div class="ctitle">{{ $product->name }}</div>
                            <p class="desc">{{ $product->short_description }}</p>
                            <div class="actions">
                                @if ($sel)
                                    <span class="btn is-on grow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M20 6L9 17l-5-5"/></svg>Sélectionné</span>
                                @else
                                    <form action="{{ route('pro.selection.add') }}" method="POST" class="grow" style="display:flex">
                                        @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button class="btn btn-primary btn-block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Ajouter</button>
                                    </form>
                                @endif
                                <a href="{{ route('pro.catalog.show', $product) }}" class="btn btn-ghost">Voir</a>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    @endforeach
</div>
@endsection
