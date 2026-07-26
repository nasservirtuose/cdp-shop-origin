<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ShopOrder;
use App\Models\ShopProduct;
use App\Services\Shop\ConfirmedSaleProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminOrderController extends Controller
{
    public function index()
    {
        return view('admin.orders.index', [
            'orders' => ShopOrder::with('product')->orderByDesc('id')->limit(200)->get(),
            'products' => ShopProduct::orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(ShopOrder $order)
    {
        $order->load('product');

        return view('admin.orders.show', ['order' => $order]);
    }

    public function simulate(Request $request, ConfirmedSaleProcessor $processor)
    {
        $data = $request->validate([
            'product_id' => 'required|integer|exists:shop_products,id',
            'paid_amount_ttc' => 'required|numeric|min:0.01',
            'origin_pro_id' => 'nullable|integer',
        ]);

        $processor->process([
            'provider' => 'TEST',
            'provider_transaction_id' => 'test_' . Str::random(12),
            'product_id' => $data['product_id'],
            'paid_amount_ttc' => $data['paid_amount_ttc'],
            'origin_pro_id' => $data['origin_pro_id'] ?: null,
            'origin_status' => $data['origin_pro_id'] ? 'MATCHED' : 'UNMATCHED',
        ]);

        return redirect()->route('admin.orders.index')->with('success', 'Vente de test enregistree.');
    }
}
