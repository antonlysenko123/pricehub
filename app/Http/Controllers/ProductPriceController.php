<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductPriceController extends Controller
{
    public function index(Request $request)
{
    $query = DB::table('product_prices');

    // Äèíàì³÷í³ ô³ëüòðè
    foreach ([
    'product_id',
    'product_name',
    'supplier_name',
    'supplier_sku',
    'price',
    'quantity'
] as $field) {

        if ($request->filled($field)) {
            $query->where($field, 'like', '%' . $request->$field . '%');
        }
    }

    $prices = $query
        ->orderBy('product_id')
        ->paginate(50)
        ->withQueryString();

    return view('price_stack.index', compact('prices'));
}
}