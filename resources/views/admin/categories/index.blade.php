@extends('admin.layout')

@section('title', 'Categories')

@section('content')
<div style="display:flex;justify-content:space-between;align-items:flex-end;margin-bottom:24px">
    <div><div class="eyebrow">Catalogue</div><div class="h1">Categories</div></div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">+ Nouvelle categorie</a>
</div>

@if ($categories->isEmpty())
    <p class="empty">Aucune categorie pour l'instant. Cree la premiere.</p>
@else
    <div class="list">
        @foreach ($categories as $c)
            <div class="row">
                <div>
                    <span class="nm">{{ $c->name }}</span>
                    <span class="q">. {{ $c->products_count }} produit(s) . {{ $c->is_active ? 'actif' : 'inactif' }}</span>
                </div>
                <div style="display:flex;gap:10px;align-items:center">
                    <a href="{{ route('admin.categories.edit', $c) }}" class="btn btn-ghost" style="padding:7px 13px">Modifier</a>
                    <form action="{{ route('admin.categories.destroy', $c) }}" method="POST" onsubmit="return confirm('Supprimer cette categorie ?')">@csrf @method('DELETE')<button class="btn-danger-link">Supprimer</button></form>
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
