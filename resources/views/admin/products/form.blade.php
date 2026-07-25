@extends('admin.layout')

@section('title', $product->exists ? 'Modifier un produit' : 'Nouveau produit')

@section('content')
@php
    $modeLabels = [
        'DIRECT_SHOP' => 'Vente directe (Kooneo)',
        'EXTERNAL_AFFILIATE' => 'Affiliation externe',
        'PARTNER' => 'Partenaire',
    ];
@endphp

<a href="{{ route('admin.products.index') }}" class="back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>Retour aux produits</a><div class="h1" style="margin:10px 0 24px">{{ $product->exists ? 'Modifier le produit' : 'Nouveau produit' }}</div>

@if ($errors->any())<div class="flash flash-err">{{ $errors->first() }}</div>@endif

<div class="panel" style="max-width:640px">
    <form action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" method="POST">
        @csrf
        @if ($product->exists) @method('PUT') @endif

        <label class="lbl" style="margin-top:0">Nom</label>
        <input class="field" type="text" name="name" value="{{ old('name', $product->name) }}" required>

        <label class="lbl">Slug <span style="font-weight:400;color:var(--faint)">(laisse vide pour auto)</span></label>
        <input class="field" type="text" name="slug" value="{{ old('slug', $product->slug) }}">

        <label class="lbl">Categorie</label>
        <select class="field" name="category_id">
            <option value="">- Aucune -</option>
            @foreach ($categories as $cat)
                <option value="{{ $cat->id }}" {{ (string) old('category_id', $product->category_id) === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
            @endforeach
        </select>

        <label class="lbl">Image (URL)</label>
        <input class="field" type="url" name="main_image" value="{{ old('main_image', $product->main_image) }}" placeholder="https://...">

        <label class="lbl">Description courte</label>
        <input class="field" type="text" name="short_description" value="{{ old('short_description', $product->short_description) }}" maxlength="255">

        <label class="lbl">Description complete</label>
        <textarea class="field" name="description" rows="4" style="height:auto;padding:10px 13px">{{ old('description', $product->description) }}</textarea>

        <label class="lbl">Mode de vente</label>
        @php $currentMode = old('commerce_mode', $product->commerce_mode?->value ?? 'DIRECT_SHOP'); @endphp
        <select class="field" name="commerce_mode">
            @foreach ($modes as $mode)
                <option value="{{ $mode->value }}" {{ $currentMode === $mode->value ? 'selected' : '' }}>{{ $modeLabels[$mode->value] ?? $mode->value }}</option>
            @endforeach
        </select>

        <label class="lbl">Lien d'achat externe <span style="font-weight:400;color:var(--faint)">(affiliation/partenaire, optionnel)</span></label>
        <input class="field" type="url" name="external_checkout_url" value="{{ old('external_checkout_url', $product->external_checkout_url) }}" placeholder="https://...">

        <label style="display:flex;align-items:center;gap:9px;margin:18px 0 8px;font-weight:600;cursor:pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
            Produit actif
        </label>
        <label style="display:flex;align-items:center;gap:9px;margin:0 0 22px;font-weight:600;cursor:pointer">
            <input type="checkbox" name="is_public" value="1" {{ old('is_public', $product->is_public ?? false) ? 'checked' : '' }}>
            Visible publiquement
        </label>

        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">{{ $product->exists ? 'Enregistrer' : 'Creer' }}</button>
            <a href="{{ route('admin.products.index') }}" class="btn btn-ghost">Annuler</a>
        </div>
    </form>
</div>
@endsection
