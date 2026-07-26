@extends('admin.layout')

@section('title', 'Commandes')

@section('content')
@php
    $badge = [
        'DRAWN' => ['Recompense tiree', 'var(--green)'],
        'REWARD_ECONOMICS_INVALID' => ['Economie invalide', 'var(--red)'],
        'NO_PRO' => ['Aucun pro', 'var(--faint)'],
    ];
@endphp
<div class="eyebrow">Ventes</div>
<div class="h1" style="margin-bottom:6px">Commandes</div>
<p class="desc" style="margin-bottom:20px">Chaque vente confirmee + la recompense tiree pour le pro (gravee, jamais recalculee).</p>

<div class="panel" style="margin-bottom:24px">
    <div style="font-weight:700;margin-bottom:12px">Simuler une vente de test</div>
    <p class="hint" style="margin:0 0 12px">Pour voir le moteur Rex tirer une recompense, sans Kooneo. Choisis un produit configure (economie + tranches).</p>
    <form action="{{ route('admin.orders.simulate') }}" method="POST" class="form-row">
        @csrf
        <select class="field" name="product_id" required style="flex:2">
            <option value="">- Produit -</option>
            @foreach ($products as $p)
                <option value="{{ $p->id }}">{{ $p->name }}</option>
            @endforeach
        </select>
        <input class="field" type="number" step="0.01" min="0.01" name="paid_amount_ttc" placeholder="Montant paye (€)" required>
        <input class="field" type="number" name="origin_pro_id" placeholder="ID pro (optionnel)">
        <button class="btn btn-primary">Simuler</button>
    </form>
</div>

@if ($orders->isEmpty())
    <p class="empty">Aucune commande pour l'instant. Simule une vente ci-dessus pour tester.</p>
@else
    <table style="width:100%;border-collapse:collapse;font-size:14.5px">
        <thead>
            <tr style="text-align:left;color:var(--muted);font-size:12.5px;border-bottom:1px solid var(--border)">
                <th style="padding:10px 8px">Date</th>
                <th style="padding:10px 8px">Produit</th>
                <th style="padding:10px 8px">Paye</th>
                <th style="padding:10px 8px">Pro</th>
                <th style="padding:10px 8px">Recompense</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        @foreach ($orders as $o)
            @php $b = $badge[$o->reward_status] ?? [$o->reward_status, 'var(--faint)']; @endphp
            <tr style="border-bottom:1px solid var(--border)">
                <td style="padding:10px 8px">{{ $o->paid_at?->format('d/m/Y H:i') }}</td>
                <td style="padding:10px 8px;font-weight:600">{{ $o->product?->name ?? '?' }}</td>
                <td style="padding:10px 8px">{{ number_format($o->amount_cents / 100, 2, ',', ' ') }} €</td>
                <td style="padding:10px 8px">{{ $o->origin_pro_id ?? '-' }}</td>
                <td style="padding:10px 8px">
                    <span style="display:inline-block;font-size:12px;font-weight:700;color:{{ $b[1] }}">{{ $b[0] }}</span>
                    @if ($o->reward_amount)
                        <span style="font-weight:700"> · {{ number_format($o->reward_amount, 2, ',', ' ') }} €</span>
                        <span style="color:var(--faint);font-size:12px">(tranche {{ $o->reward_tier }})</span>
                    @endif
                </td>
                <td style="padding:10px 8px"><a href="{{ route('admin.orders.show', $o) }}" class="btn btn-ghost" style="padding:6px 12px">Detail</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endif
@endsection
