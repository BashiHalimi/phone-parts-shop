<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    /**
     * Overview of current stock levels for all products.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $filter = $request->input('filter'); // low | out | ok

        $products = Product::query()
            ->with(['category', 'brand'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('name', 'like', "%{$search}%")
                      ->orWhere('sku', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%");
                });
            })
            ->when($filter === 'low', fn ($q) => $q->whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0))
            ->when($filter === 'out', fn ($q) => $q->where('quantity', '<=', 0))
            ->when($filter === 'ok',  fn ($q) => $q->whereColumn('quantity', '>', 'minimum_stock'))
            ->orderBy('quantity')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Summary counts
        $totalProducts = Product::count();
        $lowStockCount = Product::whereColumn('quantity', '<=', 'minimum_stock')->where('quantity', '>', 0)->count();
        $outOfStockCount = Product::where('quantity', '<=', 0)->count();
        $stockValue = Product::selectRaw('SUM(quantity * purchase_price) as value')->value('value') ?? 0;

        return view('admin.stock.index', compact(
            'products',
            'search',
            'filter',
            'totalProducts',
            'lowStockCount',
            'outOfStockCount',
            'stockValue'
        ));
    }

    /**
     * List of all stock movements with filters.
     */
    public function movements(Request $request)
    {
        $search    = $request->input('search');
        $type      = $request->input('type');
        $productId = $request->input('product_id');
        $dateFrom  = $request->input('date_from');
        $dateTo    = $request->input('date_to');

        $movements = StockMovement::query()
            ->with(['product', 'user'])
            ->when($search, function ($q) use ($search) {
                $q->whereHas('product', fn ($w) => $w->where('name', 'like', "%{$search}%")->orWhere('sku', 'like', "%{$search}%"));
            })
            ->when($type, fn ($q) => $q->where('type', $type))
            ->when($productId, fn ($q) => $q->where('product_id', $productId))
            ->when($dateFrom, fn ($q) => $q->whereDate('created_at', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('created_at', '<=', $dateTo))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name', 'sku']);

        return view('admin.stock.movements', compact('movements', 'products', 'search', 'type', 'productId', 'dateFrom', 'dateTo'));
    }

    /**
     * Show the manual adjustment form.
     */
    public function adjustForm(Product $product)
    {
        return view('admin.stock.adjust', compact('product'));
    }

    /**
     * Apply a manual stock adjustment.
     */
public function adjust(Request $request, Product $product)
{
    $data = $request->validate([
        'type'     => ['required', 'in:adjustment,damage,return'],
        'mode'     => ['required', 'in:add,set,subtract'],
        'quantity' => ['required', 'integer', 'min:1'],
        'notes'    => ['nullable', 'string', 'max:500'],
    ], [
        'quantity.min' => 'Quantity must be at least 1.',
    ]);

    try {
        DB::transaction(function () use ($product, $data) {
            $before = $product->quantity;
            $delta  = 0;

            if ($data['mode'] === 'add') {
                $delta = $data['quantity'];
            } elseif ($data['mode'] === 'subtract') {
                $delta = -$data['quantity'];

                if ($before + $delta < 0) {
                    throw new \RuntimeException('Cannot subtract more than the current stock (' . $before . ').');
                }
            } else { // set
                $delta = $data['quantity'] - $before;
            }

            $product->update(['quantity' => $before + $delta]);

            if ($delta !== 0) {
                StockMovement::create([
                    'product_id'     => $product->id,
                    'user_id'        => Auth::id(),
                    'type'           => $data['type'],
                    'quantity'       => $delta,
                    'reference_type' => null,
                    'reference_id'   => null,
                    'notes'          => $data['notes'] ?? ('Manual ' . $data['mode']),
                ]);
            }
        });
    } catch (\Throwable $e) {
        return back()
            ->withInput()
            ->with('error', $e->getMessage());
    }

    return redirect()
        ->route('admin.stock.index')
        ->with('success', 'Stock adjusted successfully for ' . $product->name . '.');
}
}