<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\MatrixSync;
use App\Jobs\SyncMatrixJob;
use App\Jobs\ImportMatrixJob;


class MatrixController extends Controller
{

public function import()
{
    $sync = MatrixSync::create([
        'status' => 'queued',
        'progress' => 0,
    ]);

    ImportMatrixJob::dispatch($sync->id);

    return back()->with('status', 'Matrix import started.');
}

    public function index(Request $request)
    {
        $query = DB::table('supplier_product_matches');

       foreach ([
    'product_id',
    'product_name',
    'supplier_name',
    'supplier_sku',

    'current_price',
    'current_quantity'
] as $field) {

            if ($request->filled($field)) {
                $query->where($field, 'like', '%' . $request->$field . '%');
            }
        }

        if ($request->boolean('only_with_price')) {
            $query->whereNotNull('current_price');
        }

        if ($request->boolean('only_without_price')) {
            $query->whereNull('current_price');
        }

        $rows = $query
            ->orderBy('product_id')
            ->paginate(50)
            ->withQueryString();

        return view('matrix.index', compact('rows'));
    }

    public function sync()
    {
        $sync = MatrixSync::create([
            'status' => 'queued',
            'progress' => 0,
        ]);

        SyncMatrixJob::dispatch($sync->id);

        return back()->with('status', 'Синхронізацію матриці поставлено в чергу.');
    }

    public function statuses()
    {
        $sync = MatrixSync::latest()->first();

        if (!$sync) {
            return response()->json(null);
        }

        return response()->json([
            'status' => (string) $sync->status,
            'progress' => (int) $sync->progress,
            'currentAction' => (string) ($sync->current_action ?? ''),
            'total' => (int) $sync->total,
            'processed' => (int) $sync->processed,
            'updatedAt' => optional($sync->updated_at)->format('d.m.Y H:i'),
        ]);
    }
}