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

        <label class="lbl">Reference externe (Kooneo)</label>
        <input class="field" type="text" id="external_reference" name="external_reference" value="{{ old('external_reference', $product->external_reference ?? '') }}" required maxlength="100" placeholder="Ex: HARNAIS-001">
        <div class="hint">Doit correspondre exactement a la reference du produit dans Kooneo pour que le webhook matche.</div>
        @error('external_reference')<div class="flash flash-err">{{ $message }}</div>@enderror

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
            <div style="font-weight:800;font-size:18px;letter-spacing:-.01em">Économie du produit</div>
            <p class="desc" style="margin:4px 0 6px">Ces chiffres servent à calculer combien le pro peut gagner. Laisse tout vide si ce produit ne donne aucune récompense.</p>
            <div style="background:var(--tint);border-radius:10px;padding:12px 14px;margin:10px 0 16px;font-size:14.5px;color:var(--green-800);line-height:1.6">
                <strong>Comment le calcul marche :</strong><br>
                Prix HT = montant payé ÷ (1 + TVA). Marge = Prix HT − coût d'achat − coûts variables. Budget Rex = Marge × Part Rex. La récompense est tirée entre la <strong>borne basse</strong> et le plus petit de (borne haute, budget Rex). Si la marge est ≤ 0, aucune récompense n'est donnée.
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px 12px">
                <div>
                    <label class="lbl" style="margin-top:0">Prix TTC normal (€)</label>
                    <input class="field" type="number" step="0.01" min="0" name="price_ttc" value="{{ old('price_ttc', $product->price_ttc) }}">
                    <div class="hint">Le prix de vente affiché, TVA comprise. Indicatif — le calcul réel se fait sur le montant vraiment payé.</div>
                </div>
                <div>
                    <label class="lbl" style="margin-top:0">TVA (%)</label>
                    <input class="field" type="number" step="0.01" min="0" max="100" name="vat_percent" value="{{ old('vat_percent', $product->vat_percent) }}">
                    <div class="hint">Le taux de TVA du produit (ex. 20 pour 20 %). Sert à retrouver le prix hors taxes.</div>
                </div>
                <div>
                    <label class="lbl" style="margin-top:0">Coût d'achat HT (€)</label>
                    <input class="field" type="number" step="0.01" min="0" name="purchase_cost_ht" value="{{ old('purchase_cost_ht', $product->purchase_cost_ht) }}">
                    <div class="hint">Ce que le produit te coûte à l'achat (hors taxes), payé au fournisseur.</div>
                </div>
                <div>
                    <label class="lbl" style="margin-top:0">Coûts variables HT (€)</label>
                    <input class="field" type="number" step="0.01" min="0" name="variable_costs_ht" value="{{ old('variable_costs_ht', $product->variable_costs_ht) }}">
                    <div class="hint">Les autres frais par vente (livraison, emballage, commission…), hors taxes.</div>
                </div>
                <div>
                    <label class="lbl" style="margin-top:0">Part Rex (%)</label>
                    <input class="field" type="number" step="0.01" min="0" max="100" name="rex_share_percent" value="{{ old('rex_share_percent', $product->rex_share_percent) }}">
                    <div class="hint">La part de ta marge que tu acceptes de reverser au pro (ex. 50 = la moitié de la marge disponible).</div>
                </div>
                <div></div>
                <div>
                    <label class="lbl" style="margin-top:0">Borne basse (€)</label>
                    <input class="field" type="number" step="0.01" min="0" name="low_bound" value="{{ old('low_bound', $product->low_bound) }}">
                    <div class="hint">Le minimum qu'un pro peut recevoir sur ce produit. La récompense ne descend jamais en dessous.</div>
                </div>
                <div>
                    <label class="lbl" style="margin-top:0">Borne haute (€)</label>
                    <input class="field" type="number" step="0.01" min="0" name="high_bound" value="{{ old('high_bound', $product->high_bound) }}">
                    <div class="hint">Le maximum autorisé. La récompense ne dépasse jamais (et reste plafonnée par le budget Rex réel).</div>
                </div>
            </div>
        </div>

        <div style="margin-top:22px;padding-top:22px;border-top:1px solid var(--border)">
            <div style="font-weight:800;font-size:18px">Tranches de récompense Rex</div>
            <p class="desc" style="margin:4px 0 6px">Les 3 tranches créent la « surprise » : selon la chance, le pro touche une petite, une moyenne ou une grosse récompense.</p>
            <div style="background:var(--tint);border-radius:10px;padding:12px 14px;margin:10px 0 14px;font-size:14.5px;color:var(--green-800);line-height:1.6">
                <strong>Comment lire les tranches :</strong><br>
                La fourchette de récompense (entre borne basse et borne haute effective) est découpée en 3 zones exprimées en %. <strong>0 %</strong> = borne basse, <strong>100 %</strong> = borne haute. Ex. tranche 1 (0→40 %) = le bas de la fourchette ; tranche 3 (70→100 %) = le haut. La <strong>probabilité</strong> = la chance que cette tranche soit tirée. Les 3 doivent totaliser <strong>100 %</strong>.
            </div>
            <p class="desc" style="margin-bottom:12px"><strong>Exemple</strong> avec 50 / 35 / 15 : 1 fois sur 2 le pro touche le bas de la fourchette, 35 % du temps le milieu, 15 % le haut.</p>
            <table style="width:100%;border-collapse:collapse">
                <thead><tr style="text-align:left;color:var(--muted);font-size:12px">
                    <th style="padding:6px 8px">Tranche</th><th style="padding:6px 8px">Début (%)</th><th style="padding:6px 8px">Fin (%)</th><th style="padding:6px 8px">Probabilité (%)</th>
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
