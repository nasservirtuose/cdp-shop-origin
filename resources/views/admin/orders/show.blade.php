@extends('admin.layout')

@section('title', 'Commande')

@section('content')
@php
    $ecoLabels = [
        'paid_amount_ttc' => 'Montant paye TTC',
        'price_ht' => 'Prix HT',
        'vat_percent' => 'TVA (%)',
        'purchase_cost_ht' => 'Cout d\'achat HT',
        'variable_costs_ht' => 'Couts variables HT',
        'rex_share_percent' => 'Part Rex (%)',
        'margin' => 'Marge',
        'low_bound' => 'Borne basse',
        'high_bound_configured' => 'Borne haute configuree',
        'rex_budget' => 'Budget Rex',
        'high_bound_effective' => 'Borne haute effective',
    ];
@endphp

<a href="{{ route('admin.orders.index') }}" class="back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>Retour aux commandes</a>
<div class="h1" style="margin:10px 0 4px">{{ $order->product?->name ?? 'Produit ?' }}</div>
<p class="desc" style="margin-bottom:22px">{{ $order->provider }} · {{ $order->provider_transaction_id }} · {{ $order->paid_at?->format('d/m/Y H:i') }}</p>

<div class="pk-cols">
    <div class="panel">
        <div style="font-weight:800;margin-bottom:12px">Photo economique</div>
        @if ($order->economic_snapshot)
            <div class="list">
                @foreach ($order->economic_snapshot as $key => $val)
                    <div class="row"><span class="nm">{{ $ecoLabels[$key] ?? $key }}</span><span style="font-weight:700">{{ is_numeric($val) ? number_format($val, 2, ',', ' ') : $val }}</span></div>
                @endforeach
            </div>
        @else
            <p class="empty" style="padding:8px 0">Aucune photo economique (pas de pro attribue).</p>
        @endif
    </div>

    <div class="panel">
        <div style="font-weight:800;margin-bottom:12px">Photo recompense</div>
        <div class="list">
            <div class="row"><span class="nm">Statut</span><span style="font-weight:700">{{ $order->reward_status }}</span></div>
            <div class="row"><span class="nm">Montant tire</span><span style="font-weight:700">{{ $order->reward_amount ? number_format($order->reward_amount, 2, ',', ' ') . ' €' : '-' }}</span></div>
            <div class="row"><span class="nm">Tranche</span><span style="font-weight:700">{{ $order->reward_tier ?? '-' }}</span></div>
            @if ($order->reward_draw_context)
                <div class="row"><span class="nm">Fourchette de la tranche</span><span style="font-weight:700">{{ number_format($order->reward_draw_context['tier_min'], 2, ',', ' ') }} - {{ number_format($order->reward_draw_context['tier_max'], 2, ',', ' ') }} €</span></div>
                <div class="row"><span class="nm">Probabilite de la tranche</span><span style="font-weight:700">{{ $order->reward_draw_context['tier_probability'] }} %</span></div>
            @endif
        </div>
    </div>
</div>
@endsection
