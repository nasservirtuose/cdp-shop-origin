@extends('admin.layout')

@section('title', 'Produits')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px">
    <div><div class="eyebrow">Catalogue</div><div class="h1">Produits</div></div>
    <a href="{{ route('admin.products.create') }}" class="btn btn-primary">+ Nouveau produit</a>
</div>

@if ($products->isEmpty())
    <p class="empty">Aucun produit pour l'instant. Cree le premier.</p>
@else
    <div class="list">
        @foreach ($products as $p)
            <div class="row">
                <div style="display:flex;align-items:center;gap:12px">
                    @if ($p->main_image)
                        <img src="{{ $p->main_image }}" alt="" style="width:44px;height:44px;border-radius:8px;object-fit:cover;border:1px solid var(--border)">
                    @endif
                    <div>
                        <span class="nm">{{ $p->name }}</span>
                        <span class="q">. {{ $p->category?->name ?? 'sans categorie' }} . {{ $p->is_public ? 'public' : 'prive' }}{{ $p->is_active ? '' : ' . inactif' }}</span>
                    </div>
                </div>
                <div style="display:flex;gap:10px;align-items:center">
                    <a href="{{ route('admin.products.edit', $p) }}" class="btn btn-ghost" style="padding:7px 13px">Modifier</a>
                    <form action="{{ route('admin.products.destroy', $p) }}" method="POST" onsubmit="return confirm('Supprimer ce produit ?')">@csrf @method('DELETE')<button class="btn-danger-link">Supprimer</button></form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
