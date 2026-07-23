@extends('layouts.app')@section('title', $product->name)@section('content')
<div class="page">
    <div class="detail">
        <div class="detail-grid">
            <div class="d-media">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3"><path d="M5 8h14l-1 11a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 8z"/><path d="M9 8V6a3 3 0 0 1 6 0v2"/></svg>
            </div>
            <div class="d-body">
                <a href="{{ route('pro.catalog.index') }}" class="back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>Retour au catalogue</a>
                @if ($product->category)<span class="chip">{{ $product->category->name }}</span>@endif
                <div class="title">{{ $product->name }}</div>
                <p class="d-desc">{{ $product->description ?? $product->short_description }}</p>

                <div class="d-cta">
                    @if ($isSelected)
                        <form action="{{ route('pro.selection.remove') }}" method="POST" style="flex:1;display:flex">
                            @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button class="btn btn-ghost btn-block">Retirer de ma sélection</button>
                        </form>
                    @else
                        <form action="{{ route('pro.selection.add') }}" method="POST" style="flex:1;display:flex">
                            @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button class="btn btn-primary btn-block"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>Ajouter à ma sélection</button>
                        </form>
                    @endif
                    <form action="{{ $isFavorite ? route('pro.favorite.remove') : route('pro.favorite.add') }}" method="POST">
                        @csrf<input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button class="fav-lg {{ $isFavorite ? 'on' : '' }}" aria-label="Favori"><svg viewBox="0 0 24 24" fill="{{ $isFavorite ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.7"><path d="M12 21s-7.5-4.9-9.7-9.3C.9 8.5 2.3 5.5 5.3 5.5c1.9 0 3.1 1 3.9 2.2h.1c.8-1.2 2-2.2 3.9-2.2 3 0 4.4 3 3 6.2C19.5 16.1 12 21 12 21z"/></svg></button>
                    </form>
                </div>

                <div class="share">
                    <div class="share-lbl">Partager à vos clients</div>
                    <div class="link-row">
                        <div class="link-in"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M9 15l6-6M8 12H6a3 3 0 0 1 0-6h2M16 12h2a3 3 0 0 1 0 6h-2"/></svg><span id="share-url">{{ $shareUrl }}</span></div>
                        <button class="copy" onclick="copyShare()"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"><rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5a2 2 0 0 1 2-2h8"/></svg>Copier</button>
                    </div>
                    <div class="share-btns">
                        <a class="sb" target="_blank" href="https://wa.me/?text={{ urlencode('Découvrez ce produit : '.$shareUrl) }}"><svg class="wa" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2a10 10 0 0 0-8.5 15.2L2 22l4.9-1.4A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2s-1.2.2-3.7-.9-3.9-3.6-4-3.8-.9-1.2-.9-2.3.6-1.6.8-1.8.4-.3.6-.3h.5c.2 0 .4 0 .6.5s.7 1.8.8 1.9 0 .3 0 .5-.2.4-.4.6-.3.4-.1.7 1 1.6 2 2.1c.8.5 1.3.4 1.5.3s.7-.8.9-1.1.4-.2.6-.1 1.5.7 1.7.9.4.2.5.3 0 .6-.2 1.1z"/></svg>WhatsApp</a>
                        <a class="sb" target="_blank" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($shareUrl) }}"><svg class="fb" viewBox="0 0 24 24" fill="currentColor"><path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.7-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12h2.7l-.4 2.9h-2.3v7A10 10 0 0 0 22 12z"/></svg>Facebook</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>function copyShare(){const t=document.getElementById('share-url').textContent;navigator.clipboard.writeText(t).then(()=>alert('Lien copié !'));}</script>
@endsection
