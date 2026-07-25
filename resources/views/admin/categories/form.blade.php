@extends('admin.layout')

@section('title', $category->exists ? 'Modifier une categorie' : 'Nouvelle categorie')

@section('content')
<a href="{{ route('admin.categories.index') }}" class="back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>Retour aux categories</a><div class="h1" style="margin:10px 0 24px">{{ $category->exists ? 'Modifier la categorie' : 'Nouvelle categorie' }}</div>

@if ($errors->any())<div class="flash flash-err">{{ $errors->first() }}</div>@endif

<div class="panel" style="max-width:560px">
    <form action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" method="POST">
        @csrf
        @if ($category->exists) @method('PUT') @endif

        <label class="lbl" style="margin-top:0">Nom</label>
        <input class="field" type="text" name="name" value="{{ old('name', $category->name) }}" required>

        <label class="lbl">Slug <span style="font-weight:400;color:var(--faint)">(laisse vide pour auto)</span></label>
        <input class="field" type="text" name="slug" value="{{ old('slug', $category->slug) }}">

        <label class="lbl">Categorie parente <span style="font-weight:400;color:var(--faint)">(optionnel)</span></label>
        <select class="field" name="parent_id">
            <option value="">- Aucune -</option>
            @foreach ($categories as $opt)
                <option value="{{ $opt->id }}" {{ (string) old('parent_id', $category->parent_id) === (string) $opt->id ? 'selected' : '' }}>{{ $opt->name }}</option>
            @endforeach
        </select>

        <label class="lbl">Ordre d'affichage</label>
        <input class="field" type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" min="0">

        <label style="display:flex;align-items:center;gap:9px;margin:18px 0 22px;font-weight:600;cursor:pointer">
            <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active ?? true) ? 'checked' : '' }}>
            Categorie active
        </label>

        <div style="display:flex;gap:10px">
            <button class="btn btn-primary">{{ $category->exists ? 'Enregistrer' : 'Creer' }}</button>
            <a href="{{ route('admin.categories.index') }}" class="btn btn-ghost">Annuler</a>
        </div>
    </form>
</div>
@endsection
