@extends('admin.layout')

@section('title', $product->exists ? 'Modifier un produit' : 'Nouveau produit')

@section('content')
@php
    $modeLabels = ['DIRECT_SHOP' => 'Vente directe (Kooneo)', 'EXTERNAL_AFFILIATE' => 'Affiliation externe', 'PARTNER' => 'Partenaire'];
    $tierValues = $product->exists ? $product->rewardTiers->keyBy('tier_number') : collect();
    $defaults = [1 => [0, 40, 50], 2 => [40, 70, 35], 3 => [70, 100, 15]];
@endphp
<a href="{{ route('admin.products.index') }}" class="back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>Retour aux produits</a><div class="h1" style="margin:10px 0 24px">{{ $product->exists ? 'Modifier le produit' : 'Nouveau produit' }}</div>

@if ($errors->any())<div class="flash flash-err">{{ $errors->first() }}</div>@endif

<div class="panel" style="max-width:720px">
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

        <div style="margin-top:26px;padding-top:22px;border-top:1px solid var(--border)">
            <div style="font-weight:800;font-size:16px;letter-spacing:-.01em">Economie du produit</div>
            <p class="desc" style="margin:4px 0 14px">Sert au calcul de la recompense Rex. Laisse vide si le produit ne donne pas de recompense.</p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                <div><label class="lbl" style="margin-top:0">Prix TTC normal (EUR)</label><input class="field" type="number" step="0.01" min="0" name="price_ttc" value="{{ old('price_ttc', $product->price_ttc) }}"></div>
                <div><label class="lbl" style="margin-top:0">TVA (%)</label><input class="field" type="number" step="0.01" min="0" max="100" name="vat_percent" value="{{ old('vat_percent', $product->vat_percent) }}"></div>
                <div><label class="lbl" style="margin-top:0">Cout d'achat HT (EUR)</label><input class="field" type="number" step="0.01" min="0" name="purchase_cost_ht" value="{{ old('purchase_cost_ht', $product->purchase_cost_ht) }}"></div>
                <div><label class="lbl" style="margin-top:0">Couts variables HT (EUR)</label><input class="field" type="number" step="0.01" min="0" name="variable_costs_ht" value="{{ old('variable_costs_ht', $product->variable_costs_ht) }}"></div>
                <div><label class="lbl" style="margin-top:0">Part Rex (%)</label><input class="field" type="number" step="0.01" min="0" max="100" name="rex_share_percent" value="{{ old('rex_share_percent', $product->rex_share_percent) }}"></div>
                <div></div>
                <div><label class="lbl" style="margin-top:0">Borne basse (EUR)</label><input class="field" type="number" step="0.01" min="0" name="low_bound" value="{{ old('low_bound', $product->low_bound) }}"></div>
                <div><label class="lbl" style="margin-top:0">Borne haute (EUR)</label><input class="field" type="number" step="0.01" min="0" name="high_bound" value="{{ old('high_bound', $product->high_bound) }}"></div>
            </div>
        </div>

        <div style="margin-top:22px;padding-top:22px;border-top:1px solid var(--border)">
            <div style="font-weight:800;font-size:16px">Tranches de recompense Rex</div>
            <p class="desc" style="margin:4px 0 14px">3 tranches. La somme des probabilites doit faire <strong>100 %</strong>.</p>
            <table style="width:100%;border-collapse:collapse">
                <thead><tr style="text-align:left;color:var(--muted);font-size:12px">
                    <th style="padding:6px 8px">Tranche</th><th style="padding:6px 8px">Debut (%)</th><th style="padding:6px 8px">Fin (%)</th><th style="padding:6px 8px">Probabilite (%)</th>
                </tr></thead>
                <tbody>
                @foreach ([1, 2, 3] as $n)
                    @php $t = $tierValues->get($n); $d = $defaults[$n]; @endphp
                    <tr>
                        <td style="padding:6px 8px;font-weight:700">{{ $n }}</td>
                        <td style="padding:6px 8px"><input class="field" style="height:38px" type="number" step="0.01" min="0" max="100" name="tiers[{{ $n }}][range_start_percentage]" value="{{ old("tiers.$n.range_start_percentage", $t->range_start_percentage ?? $d[0]) }}"></td>
                        <td style="padding:6px 8px"><input class="field" style="height:38px" type="number" step="0.01" min="0" max="100" name="tiers[{{ $n }}][range_end_percentage]" value="{{ old("tiers.$n.range_end_percentage", $t->range_end_percentage ?? $d[1]) }}"></td>
                        <td style="padding:6px 8px"><input class="field" style="height:38px" type="number" step="1" min="0" max="100" name="tiers[{{ $n }}][probability_percentage]" value="{{ old("tiers.$n.probability_percentage", $t->probability_percentage ?? $d[2]) }}"></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>

        <label style="display:flex;align-items:center;gap:9px;margin:22px 0 8px;font-weight:600;cursor:pointer">
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
