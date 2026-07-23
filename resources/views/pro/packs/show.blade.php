@extends('layouts.app')@section('title', $pack->name)@section('content')
<div class="page">
    <a href="{{ route('pro.selection.index') }}" class="back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 5l-7 7 7 7"/></svg>Retour à Ma sélection</a>
    @if (session('success'))<div class="flash flash-ok">{{ session('success') }}</div>@endif    @if (session('error'))<div class="flash flash-err">{{ session('error') }}</div>@endif
    <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:16px;margin:8px 0 26px">
        <div>
            <div class="eyebrow">Pack</div>
            <div class="h1">{{ $pack->name }}</div>
            @if ($pack->description)<p class="lede">{{ $pack->description }}</p>@endif
        </div>
        <form action="{{ route('pro.packs.destroy', $pack) }}" method="POST" onsubmit="return confirm('Supprimer ce pack ?')">
            @csrf @method('DELETE')
            <button class="btn-danger-link"><svg class="i" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 7h16M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M6 7l1 13a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1l1-13"/></svg>Supprimer</button>
        </form>
    </div>
    <div class="pk-cols">
        <div class="panel">
            <div style="font-weight:700;margin-bottom:14px">Produits du pack ({{ $pack->items->count() }})</div>
            @if ($pack->items->isEmpty())
                <p class="empty" style="padding:8px 0">Ce pack est vide. Ajoute des produits depuis ta sélection →</p>
            @else
                <div class="list">
                    @foreach ($pack->items as $item)
                        <div class="row">
                            <span class="nm">{{ $item->product->name ?? '?' }} <span class="q">×{{ $item->quantity }}</span></span>
                            <form action="{{ route('pro.packs.items.remove', $pack) }}" method="POST">@csrf<input type="hidden" name="product_id" value="{{ $item->product_id }}"><button class="btn-danger-link">Retirer</button></form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
        <div class="panel">
            <div style="font-weight:700;margin-bottom:14px">Ajouter depuis Ma sélection</div>
            @if ($selections->isEmpty())
                <p class="empty" style="padding:8px 0">Ta sélection est vide. <a href="{{ route('pro.catalog.index') }}">Parcourir le catalogue</a>.</p>
            @else
                <div class="list">
                    @foreach ($selections as $s) @if ($s->product)
                        <div class="row">
                            <span class="nm">{{ $s->product->name }}</span>
                            <form action="{{ route('pro.packs.items.add', $pack) }}" method="POST" style="display:flex;gap:8px;align-items:center">
                                @csrf<input type="hidden" name="product_id" value="{{ $s->product_id }}">
                                <input class="field" style="width:64px;height:36px" type="number" name="quantity" value="1" min="1">
                                <button class="btn btn-primary" style="padding:8px 14px">Ajouter</button>
                            </form>
                        </div>
                    @endif @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
                        @if ($selection->product)
                            <div class="border rounded p-3 bg-gray-50 flex justify-between items-center">
                                <span>{{ $selection->product->name }}</span>
                                <form action="{{ route('pro.packs.items.add', $pack) }}" method="POST" class="flex gap-2 items-center">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $selection->product_id }}">
                                    <input type="number" name="quantity" value="1" min="1" class="w-16 border rounded px-2 py-1 text-sm">
                                    <button class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">+ Pack</button>
                                </form>
                            </div>
                        @endif
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
