<?php

namespace App\Http\Controllers\Pro;

use App\Http\Controllers\Controller;
use App\Models\ProPack;
use App\Services\ProPackService;
use App\Services\ProProductSelectionService;
use App\Support\CurrentPro;
use Illuminate\Http\Request;

class ProPackController extends Controller
{
    public function __construct(
        private ProPackService $packService,
        private ProProductSelectionService $selectionService,
    ) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $pack = $this->packService->create(
            CurrentPro::id(),
            $validated['name'],
            $validated['description'] ?? null
        );

        return redirect()->route('pro.packs.show', $pack)->with('success', 'Pack créé.');
    }

    public function show(ProPack $pack)
    {
        $proId = CurrentPro::id();
        abort_if($pack->pro_id !== $proId, 403); // sécurité : pas le pack d'un autre pro
        $pack->load('items.product');

        return view('pro.packs.show', [
            'pack'       => $pack,
            'selections' => $this->selectionService->list($proId),
        ]);
    }

    public function addItem(Request $request, ProPack $pack)
    {
        abort_if($pack->pro_id !== CurrentPro::id(), 403);

        $validated = $request->validate([
            'product_id' => 'required|integer',
            'quantity'   => 'nullable|integer|min:1',
        ]);

        $item = $this->packService->addItem($pack->id, $validated['product_id'], $validated['quantity'] ?? 1);

        if (!$item) {
            return back()->with('error', "Le produit doit d'abord être dans Ma sélection.");
        }

        return back()->with('success', 'Produit ajouté au pack.');
    }

    public function removeItem(Request $request, ProPack $pack)
    {
        abort_if($pack->pro_id !== CurrentPro::id(), 403);

        $validated = $request->validate(['product_id' => 'required|integer']);
        $this->packService->removeItem($pack->id, $validated['product_id']);

        return back()->with('success', 'Produit retiré du pack.');
    }

    public function destroy(ProPack $pack)
    {
        abort_if($pack->pro_id !== CurrentPro::id(), 403);

        $pack->delete();

        return redirect()->route('pro.selection.index')->with('success', 'Pack supprimé.');
    }
}
